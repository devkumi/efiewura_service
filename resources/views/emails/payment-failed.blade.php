@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-urgent">
    Payment Failed
</div>

<h2 class="message-title">Payment Could Not Be Processed</h2>

<div class="message-content">
    <p>Hello {{ $booking->tenant_name }},</p>

    <p>Unfortunately, your payment for <strong>{{ $booking->property->title }}</strong> could not be completed.</p>

    @if(!empty($notification->data['failure_reason']))
    <p><strong>Reason:</strong> {{ $notification->data['failure_reason'] }}</p>
    @endif
</div>

<div class="details-box">
    <h3>Payment Attempt Details</h3>
    <div class="detail-item">
        <span class="detail-label">Reference:</span>
        <span class="detail-value">{{ $notification->data['reference'] ?? 'N/A' }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Amount:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($notification->data['amount'] ?? 0, 2) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $booking->property->title }}</span>
    </div>
</div>

<div class="message-content">
    <p><strong>What to do next:</strong></p>
    <ol style="margin: 15px 0; padding-left: 20px;">
        <li>Check that your card or mobile money account has sufficient funds</li>
        <li>Verify your payment details are correct</li>
        <li>Try booking again from the Efiewura platform</li>
        <li>Contact your bank or mobile money provider if the issue persists</li>
    </ol>

    <p>Your booking request has been cancelled. You are welcome to submit a new booking request once you are ready to complete payment.</p>

    <p>If you believe this is an error, please contact our support team.</p>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/properties" class="cta-button">
        Browse Properties
    </a>
</div>
@endsection
