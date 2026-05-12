@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">💰 {{ $notification->title }}</h2>

<div class="message-content">
    <p>Hello {{ $user->name }},</p>
    
    <p><strong>{{ $notification->message }}</strong></p>
    
    @if(strpos($notification->type, '5_days') !== false)
        <p>This is a friendly reminder that your rent payment is due in 5 days. Planning ahead helps avoid late fees and ensures a smooth rental experience.</p>
    @elseif(strpos($notification->type, 'due_today') !== false)
        <p><strong>Your rent payment is due today.</strong> Please submit your payment as soon as possible to avoid any late fees or complications.</p>
    @else
        <p><strong>URGENT:</strong> Your rent payment is now overdue. Please submit payment immediately to avoid potential lease termination and additional fees.</p>
        <p style="color: #dc3545; font-weight: bold;">⚠️ Continued non-payment may result in automatic lease cancellation.</p>
    @endif
</div>

<div class="details-box">
    <h3>Payment Information</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $notification->data['property_title'] }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Amount Due:</span>
        <span class="detail-value">{{ $notification->data['currency'] }} {{ number_format($notification->data['monthly_rent'], 2) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Due Date:</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($notification->data['due_date'])->format('F j, Y') }}</span>
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
    <a href="{{ config('app.url') }}/bookings/{{ $notification->data['booking_id'] }}/payment" class="cta-button">
        Make Payment Now
    </a>
</div>

<div class="message-content">
    <p><strong>Payment Methods:</strong></p>
    <ul style="margin: 15px 0; padding-left: 20px;">
        <li>Contact your landlord directly at {{ $notification->data['landlord_phone'] }}</li>
        <li>Use the payment methods agreed upon in your lease</li>
        <li>Ensure you get a receipt for your payment</li>
    </ul>
    
    @if(strpos($notification->type, 'overdue') !== false)
        <p><strong>If you're experiencing financial difficulties:</strong> Please contact your landlord immediately to discuss payment arrangements. Communication is key to finding a solution.</p>
    @endif
</div>
@endsection
