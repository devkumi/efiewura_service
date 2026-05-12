@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">🏠 {{ $notification->title }}</h2>

<div class="message-content">
    @if($notification->data['recipient_type'] === 'tenant')
        <p>Hello {{ $user->name }},</p>
        
        <p><strong>{{ $notification->message }}</strong></p>
        
        <p>Due to {{ $notification->data['days_past_due'] }} days of overdue rent payments, your lease has been automatically cancelled and the property has been released back to the market.</p>
        
        <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <p style="margin: 0; color: #721c24;"><strong>⚠️ Important:</strong> You are required to vacate the property immediately. Please arrange to collect your belongings and return all keys and access items.</p>
        </div>
        
        <p><strong>What happens next:</strong></p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>You must vacate the property immediately</li>
            <li>Return all keys, remotes, and access cards to the landlord</li>
            <li>Arrange for collection of your personal belongings</li>
            <li>Contact the landlord to discuss outstanding payment arrangements</li>
        </ul>
        
        <p><strong>If you believe this is an error:</strong> Please contact our support team immediately at support@efiewura.com or your landlord directly.</p>
    @else
        <p>Hello {{ $user->name }},</p>
        
        <p><strong>{{ $notification->message }}</strong></p>
        
        <p>Your property has been automatically released back to the market due to the tenant's payment being {{ $notification->data['days_past_due'] }} days overdue.</p>
        
        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <p style="margin: 0; color: #155724;"><strong>✅ Property Status:</strong> Your property is now available for new bookings and visible to potential tenants.</p>
        </div>
        
        <p><strong>Next steps:</strong></p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Property is now available for new tenant applications</li>
            <li>Coordinate with the previous tenant for key collection and property handover</li>
            <li>Consider updating your property listing or pricing if needed</li>
            <li>Review your overdue payment collection process with the previous tenant</li>
        </ul>
    @endif
</div>

<div class="details-box">
    <h3>Property Information</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $notification->data['property_title'] }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Days Past Due:</span>
        <span class="detail-value">{{ $notification->data['days_past_due'] }} days</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Monthly Rent:</span>
        <span class="detail-value">{{ $notification->data['currency'] }} {{ number_format($notification->data['monthly_rent'], 2) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Cancellation Reason:</span>
        <span class="detail-value">{{ $notification->data['cancellation_reason'] }}</span>
    </div>
</div>

<div style="text-align: center;">
    @if($notification->data['recipient_type'] === 'tenant')
        <a href="{{ config('app.url') }}/properties" class="cta-button">
            Find New Property
        </a>
    @else
        <a href="{{ config('app.url') }}/my-properties" class="cta-button">
            Manage My Properties
        </a>
    @endif
</div>

<div class="message-content">
    @if($notification->data['recipient_type'] === 'tenant')
        <p><strong>Need Help?</strong> If you're experiencing financial difficulties, please reach out to our support team. We may be able to help you find alternative housing solutions.</p>
    @else
        <p><strong>Questions?</strong> Contact our support team if you need assistance with the property release process or have questions about finding new tenants.</p>
    @endif
</div>
@endsection
