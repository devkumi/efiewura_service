@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">📦 {{ $notification->title }}</h2>

<div class="message-content">
    <p>Hello {{ $user->name }},</p>
    
    <p><strong>{{ $notification->message }}</strong></p>
    
    <p>Your lease is coming to an end in 7 days. Here's your move-out preparation checklist:</p>
</div>

<div class="details-box">
    <h3>Move-out Checklist</h3>
    <div style="margin: 20px 0;">
        <p><strong>✅ This Week (7 days before):</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Contact your landlord to schedule final inspection</li>
            <li>Arrange for moving truck and helpers</li>
            <li>Begin packing non-essential items</li>
            <li>Notify utility companies of your move</li>
            <li>Update your address with banks, employers, etc.</li>
        </ul>
        
        <p><strong>🔍 Property Preparation:</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Clean the property thoroughly</li>
            <li>Repair any minor damages (if responsible)</li>
            <li>Remove all personal belongings</li>
            <li>Return keys, remotes, and access cards</li>
        </ul>
        
        <p><strong>💰 Security Deposit:</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Review lease terms for deposit return conditions</li>
            <li>Document property condition with photos</li>
            <li>Provide forwarding address for deposit return</li>
        </ul>
    </div>
</div>

<div class="details-box">
    <h3>Lease Information</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $notification->data['property_title'] }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Move-out Date:</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($notification->data['move_out_date'])->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Security Deposit:</span>
        <span class="detail-value">{{ $notification->data['currency'] }} {{ number_format($notification->data['security_deposit'], 2) }}</span>
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
        View Lease Details
    </a>
</div>

<div class="message-content">
    <p><strong>Important:</strong> Please contact your landlord at {{ $notification->data['landlord_phone'] }} to schedule your final inspection and coordinate the key handover process.</p>
    
    <p>Thank you for choosing Efiewura! We hope you had a great experience in your rental property.</p>
</div>
@endsection
