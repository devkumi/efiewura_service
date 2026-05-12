@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

@if($notification->type === 'move_in_today')
    <h2 class="message-title">🏠 {{ $notification->title }}</h2>
@else
    <h2 class="message-title">📅 {{ $notification->title }}</h2>
@endif

<div class="message-content">
    <p>Hello {{ $user->name }},</p>
    
    <p><strong>{{ $notification->message }}</strong></p>
    
    @if($notification->type === 'move_in_today')
        <p>🎉 Today is your move-in day! We're excited to welcome you to your new home. Here are some important reminders for today:</p>
        
        <p><strong>Before Moving In:</strong></p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Confirm your move-in appointment with your landlord</li>
            <li>Ensure you have the total amount due: {{ $notification->data['currency'] }} {{ number_format($notification->data['total_amount_due'], 2) }}</li>
            <li>Bring valid identification and any required documents</li>
            <li>Take photos of the property condition before moving in</li>
        </ul>
    @else
        <p>Your move-in date is approaching! Here's your preparation checklist to ensure a smooth transition:</p>
        
        <p><strong>7-Day Move-in Checklist:</strong></p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li><strong>Confirm appointment:</strong> Contact your landlord to schedule move-in time</li>
            <li><strong>Prepare payment:</strong> Arrange for {{ $notification->data['currency'] }} {{ number_format($notification->data['total_amount_due'], 2) }}</li>
            <li><strong>Gather documents:</strong> ID, lease agreement, and references</li>
            <li><strong>Plan logistics:</strong> Arrange moving truck, helpers, and utilities transfer</li>
            <li><strong>Property inspection:</strong> Plan to document property condition</li>
        </ul>
    @endif
</div>

<div class="details-box">
    <h3>Move-in Information</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $notification->data['property_title'] }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Move-in Date:</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($notification->data['move_in_date'])->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Total Amount Due:</span>
        <span class="detail-value">{{ $notification->data['currency'] }} {{ number_format($notification->data['total_amount_due'], 2) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Landlord:</span>
        <span class="detail-value">{{ $notification->data['landlord_name'] }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Landlord Phone:</span>
        <span class="detail-value">{{ $notification->data['landlord_phone'] }}</span>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/bookings/{{ $notification->data['booking_id'] }}" class="cta-button">
        View Booking Details
    </a>
</div>

<div class="message-content">
    @if($notification->type === 'move_in_today')
        <p><strong>Welcome Home!</strong> We hope you love your new place. If you have any questions or concerns, don't hesitate to reach out to your landlord or our support team.</p>
    @else
        <p><strong>Questions?</strong> Contact your landlord at {{ $notification->data['landlord_phone'] }} or reach out to our support team for assistance with your move-in process.</p>
    @endif
</div>
@endsection
