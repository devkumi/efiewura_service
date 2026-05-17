<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Landlord;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['webhook']);
    }

    /**
     * Verify a Paystack payment after the tenant returns from the payment page.
     * The client must POST the reference returned at booking creation.
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = Payment::where('paystack_reference', $request->reference)
            ->where('user_id', Auth::id())
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found',
            ], 404);
        }

        if ($payment->isPaid()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment already verified',
                'data' => $payment->load('booking'),
            ]);
        }

        try {
            $paystackService = new PaystackService();
            $transaction = $paystackService->verifyTransaction($request->reference);

            DB::beginTransaction();

            if ($transaction['status'] === 'success') {
                $this->handleSuccessfulPayment($payment, $transaction);
            } else {
                $this->handleFailedPayment($payment, $transaction['gateway_response'] ?? 'Payment was not successful');
            }

            DB::commit();

            return response()->json([
                'success' => $payment->isPaid(),
                'message' => $payment->isPaid() ? 'Payment verified successfully' : 'Payment was not successful',
                'data' => $payment->fresh()->load('booking.property'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment verification error: " . $e->getMessage(), ['reference' => $request->reference]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle incoming Paystack webhook events.
     * Route must be excluded from CSRF and auth:sanctum middleware.
     */
    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('X-Paystack-Signature', '');
        $payload = $request->getContent();

        $paystackService = new PaystackService();

        if (!$paystackService->validateWebhookSignature($payload, $signature)) {
            Log::warning('Invalid Paystack webhook signature');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->json('event');
        $data = $request->json('data');

        Log::info("Paystack webhook received: {$event}", ['reference' => $data['reference'] ?? null]);

        try {
            match ($event) {
                'charge.success' => $this->webhookChargeSuccess($data),
                'charge.failed' => $this->webhookChargeFailed($data),
                'refund.processed' => $this->webhookRefundProcessed($data),
                default => null,
            };
        } catch (\Exception $e) {
            Log::error("Webhook processing error for event {$event}: " . $e->getMessage());
        }

        return response()->json(['message' => 'Webhook received']);
    }

    /**
     * Get details of a single payment. Owner or admin only.
     */
    public function show(Payment $payment): JsonResponse
    {
        $user = Auth::user();

        if ($payment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $payment->load(['booking.property', 'landlord.user']),
        ]);
    }

    /**
     * List the authenticated tenant's payment history.
     */
    public function myPayments(Request $request): JsonResponse
    {
        $query = Payment::where('user_id', Auth::id())
            ->with(['booking.property'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        $payments = $query->paginate($request->get('per_page', 15));

        $totals = Payment::where('user_id', Auth::id())
            ->selectRaw('SUM(status = "paid") as paid_count, SUM(amount * (status = "paid")) as total_paid')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ],
            'summary' => [
                'paid_transactions' => (int) ($totals->paid_count ?? 0),
                'total_paid' => (float) ($totals->total_paid ?? 0),
            ],
        ]);
    }

    /**
     * Initiate a monthly rent renewal payment for a confirmed booking.
     */
    public function payRent(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($booking->status !== 'confirmed' || $booking->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed and initially paid bookings can initiate rent renewals',
            ], 400);
        }

        // Prevent duplicate pending renewal payment for the same booking
        $existingPending = Payment::where('booking_id', $booking->id)
            ->where('type', 'rent_renewal')
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return response()->json([
                'success' => false,
                'message' => 'A pending rent payment already exists for this booking. Please complete or cancel it first.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $reference = Payment::generateReference($booking->id);

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'landlord_id' => $booking->landlord_id,
                'type' => 'rent_renewal',
                'amount' => $booking->monthly_rent,
                'currency' => $booking->currency,
                'status' => 'pending',
                'paystack_reference' => $reference,
            ]);

            $paystackService = new PaystackService();
            $paystackData = $paystackService->initializeTransaction([
                'email' => $booking->tenant_email,
                'amount' => (int) round($booking->monthly_rent * 100),
                'reference' => $reference,
                'currency' => $booking->currency,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'payment_id' => $payment->id,
                    'type' => 'rent_renewal',
                    'property_title' => $booking->property->title ?? '',
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rent payment initialized. Please complete payment.',
                'data' => [
                    'payment' => $payment,
                    'reference' => $reference,
                    'amount' => $booking->monthly_rent,
                    'currency' => $booking->currency,
                    'payment_url' => $paystackData['authorization_url'],
                    'access_code' => $paystackData['access_code'],
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Rent payment init error: " . $e->getMessage(), ['booking_id' => $booking->id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize rent payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Landlord: list payments received for their properties.
     */
    public function landlordPayments(Request $request): JsonResponse
    {
        $landlord = Auth::user()->landlord;

        if (!$landlord) {
            return response()->json([
                'success' => false,
                'message' => 'Landlord profile not found',
            ], 400);
        }

        $query = Payment::where('landlord_id', $landlord->id)
            ->with(['booking.property', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        $payments = $query->paginate($request->get('per_page', 15));

        $totals = Payment::where('landlord_id', $landlord->id)
            ->paid()
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        // Monthly breakdown for the past 12 months
        $monthly = Payment::where('landlord_id', $landlord->id)
            ->paid()
            ->where('paid_at', '>=', now()->subMonths(12))
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as revenue, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ],
            'summary' => [
                'total_received' => (float) ($totals->total ?? 0),
                'transaction_count' => (int) ($totals->count ?? 0),
                'monthly_breakdown' => $monthly,
            ],
        ]);
    }

    /**
     * Admin: process a refund for a payment.
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refund_reason' => 'required|string|max:1000',
            'refund_amount' => 'nullable|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$payment->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Only paid transactions can be refunded',
            ], 400);
        }

        if ($payment->isRefunded()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment has already been refunded',
            ], 400);
        }

        $refundAmount = $request->filled('refund_amount')
            ? (float) $request->refund_amount
            : null;

        if ($refundAmount !== null && $refundAmount > $payment->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Refund amount cannot exceed the original payment amount',
            ], 422);
        }

        try {
            $paystackService = new PaystackService();
            $refundData = $paystackService->refundTransaction(
                $payment->paystack_transaction_id,
                $refundAmount
            );

            DB::beginTransaction();

            $payment->update([
                'status' => 'refunded',
                'refund_amount' => $refundAmount ?? $payment->amount,
                'refund_reference' => $refundData['transaction_reference'] ?? $refundData['id'] ?? null,
                'refunded_at' => now(),
                'refund_reason' => $request->refund_reason,
            ]);

            $booking = $payment->booking;
            if ($booking) {
                $booking->update(['payment_status' => 'refunded']);
                $booking->load(['property', 'landlord.user', 'user']);
                Notification::createRefundProcessed($booking, $payment);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => $payment->fresh()->load('booking.property'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Refund error for payment {$payment->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Refund failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function handleSuccessfulPayment(Payment $payment, array $transaction): void
    {
        $payment->update([
            'status' => 'paid',
            'paystack_transaction_id' => (string) $transaction['id'],
            'paystack_authorization_code' => $transaction['authorization']['authorization_code'] ?? null,
            'payment_channel' => $transaction['channel'] ?? null,
            'paid_at' => now(),
            'metadata' => $transaction,
        ]);

        $booking = $payment->booking;

        if (!$booking) {
            return;
        }

        $booking->update([
            'payment_status' => 'paid',
            'payment_completed_at' => now(),
        ]);

        $booking->load(['property', 'landlord.user', 'user']);

        // Only notify landlord of new booking request after first payment succeeds
        if ($payment->type === 'booking_payment') {
            Notification::createNewBookingRequest($booking);
        }

        Notification::createPaymentReceived($booking, $payment);
    }

    private function handleFailedPayment(Payment $payment, string $reason): void
    {
        $payment->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);

        $booking = $payment->booking;

        if ($booking && $payment->type === 'booking_payment') {
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Payment failed: ' . $reason,
            ]);
            $booking->load(['property', 'landlord.user', 'user']);
        }

        if ($booking) {
            Notification::createPaymentFailed($booking, $payment);
        }
    }

    private function webhookChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return;
        }

        $payment = Payment::where('paystack_reference', $reference)->first();

        if (!$payment || $payment->isPaid()) {
            return;
        }

        DB::beginTransaction();
        try {
            $this->handleSuccessfulPayment($payment, $data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function webhookChargeFailed(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return;
        }

        $payment = Payment::where('paystack_reference', $reference)->first();

        if (!$payment || $payment->status !== 'pending') {
            return;
        }

        DB::beginTransaction();
        try {
            $this->handleFailedPayment($payment, $data['gateway_response'] ?? 'Payment failed');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function webhookRefundProcessed(array $data): void
    {
        $transactionId = (string) ($data['transaction']['id'] ?? '');

        if (!$transactionId) {
            return;
        }

        $payment = Payment::where('paystack_transaction_id', $transactionId)->first();

        if (!$payment || $payment->isRefunded()) {
            return;
        }

        $payment->update([
            'status' => 'refunded',
            'refund_amount' => ($data['amount'] ?? 0) / 100,
            'refund_reference' => $data['transaction_reference'] ?? null,
            'refunded_at' => now(),
            'refund_reason' => $payment->refund_reason ?? 'Processed via Paystack',
        ]);

        $booking = $payment->booking;
        if ($booking) {
            $booking->update(['payment_status' => 'refunded']);
        }
    }
}
