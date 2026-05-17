@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-high">
    Refund Processed
</div>

<h2 class="message-title">Your Refund Has Been Processed</h2>

<div class="message-content">
    <p>Hello {{ $booking->tenant_name }},</p>

    <p>A refund has been initiated for your booking of <strong>{{ $booking->property->title }}</strong>.</p>
</div>

<div class="details-box">
    <h3>Refund Details</h3>
    <div class="detail-item">
        <span class="detail-label">Refund Amount:</span>
        <span class="detail-value"><strong>{{ $booking->currency }} {{ number_format($notification->data['refund_amount'] ?? 0, 2) }}</strong></span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Original Payment:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($notification->data['original_amount'] ?? 0, 2) }}</span>
    </div>
    @if(!empty($notification->data['refund_reference']))
    <div class="detail-item">
        <span class="detail-label">Refund Reference:</span>
        <span class="detail-value">{{ $notification->data['refund_reference'] }}</span>
    </div>
    @endif
    @if(!empty($notification->data['refund_reason']))
    <div class="detail-item">
        <span class="detail-label">Reason:</span>
        <span class="detail-value">{{ $notification->data['refund_reason'] }}</span>
    </div>
    @endif
    <div class="detail-item">
        <span class="detail-label">Processed On:</span>
        <span class="detail-value">{{ $notification->created_at->format('F j, Y \a\t g:i A') }}</span>
    </div>
</div>

<div class="details-box">
    <h3>Booking Details</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $booking->property->title }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Booking ID:</span>
        <span class="detail-value">#{{ $booking->id }}</span>
    </div>
</div>

<div class="message-content">
    <p><strong>Please note:</strong> Refunds typically take <strong>3–5 business days</strong> to reflect in your account, depending on your bank or mobile money provider.</p>

    <p>If you have not received your refund after 7 business days, please contact our support team with the refund reference above.</p>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/properties" class="cta-button">
        Browse Properties
    </a>
</div>
@endsection
