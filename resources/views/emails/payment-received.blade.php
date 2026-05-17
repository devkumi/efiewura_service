@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-high">
    Payment Confirmed
</div>

<h2 class="message-title">Payment Received Successfully</h2>

<div class="message-content">
    <p>Hello {{ $booking->tenant_name }},</p>

    <p>Your payment has been processed and confirmed. Thank you!</p>
</div>

<div class="details-box">
    <h3>Payment Details</h3>
    <div class="detail-item">
        <span class="detail-label">Reference:</span>
        <span class="detail-value">{{ $notification->data['reference'] ?? 'N/A' }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Amount Paid:</span>
        <span class="detail-value"><strong>{{ $booking->currency }} {{ number_format($notification->data['amount'] ?? 0, 2) }}</strong></span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Payment Type:</span>
        <span class="detail-value">{{ ucwords(str_replace('_', ' ', $notification->data['payment_type'] ?? '')) }}</span>
    </div>
    @if(!empty($notification->data['channel']))
    <div class="detail-item">
        <span class="detail-label">Payment Channel:</span>
        <span class="detail-value">{{ ucfirst($notification->data['channel']) }}</span>
    </div>
    @endif
    <div class="detail-item">
        <span class="detail-label">Date:</span>
        <span class="detail-value">{{ $notification->created_at->format('F j, Y \a\t g:i A') }}</span>
    </div>
</div>

<div class="details-box">
    <h3>Property & Booking</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $booking->property->title }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Address:</span>
        <span class="detail-value">{{ $booking->property->address }}, {{ $booking->property->city }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Move-in Date:</span>
        <span class="detail-value">{{ $booking->move_in_date->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Monthly Rent:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->monthly_rent, 2) }}</span>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/bookings/{{ $booking->id }}" class="cta-button">
        View Booking
    </a>
</div>

<div class="message-content">
    <p>Your booking request has been forwarded to the landlord. You will receive another notification once they review and confirm it.</p>
    <p>Keep this email as your payment receipt.</p>
</div>
@endsection
