@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">⏰ {{ $notification->title }}</h2>

<div class="message-content">
    <p>Hello {{ $user->name }},</p>
    
    <p><strong>{{ $notification->message }}</strong></p>
    
    @if($notification->data['reminder_days'] == 60)
        <p>Your lease expires in 2 months. Now is a great time to:</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Contact your landlord to discuss renewal terms</li>
            <li>Review your lease agreement</li>
            <li>Consider your housing options</li>
            <li>Plan ahead for a smooth transition</li>
        </ul>
    @elseif($notification->data['reminder_days'] == 30)
        <p><strong>Action Required:</strong> Your lease expires in 30 days. Please:</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li><strong>Contact your landlord immediately</strong> about renewal</li>
            <li>Confirm your move-out plans if not renewing</li>
            <li>Schedule a property inspection</li>
            <li>Begin organizing your move if necessary</li>
        </ul>
    @else
        <p><strong>URGENT ACTION REQUIRED:</strong> Your lease expires in 1 week!</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li><strong>Finalize renewal immediately</strong> or confirm move-out</li>
            <li>Complete final property inspection</li>
            <li>Arrange for key handover</li>
            <li>Ensure all personal belongings are ready for move</li>
        </ul>
    @endif
</div>

<div class="details-box">
    <h3>Lease Information</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $notification->data['property_title'] }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Tenant:</span>
        <span class="detail-value">{{ $notification->data['tenant_name'] }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Lease End Date:</span>
        <span class="detail-value">{{ \Carbon\Carbon::parse($notification->data['move_out_date'])->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Lease Duration:</span>
        <span class="detail-value">{{ $notification->data['lease_duration_months'] }} months</span>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/bookings/{{ $notification->data['booking_id'] }}" class="cta-button">
        View Lease Details
    </a>
</div>

<div class="message-content">
    <p><strong>Need Help?</strong> Contact our support team if you have any questions about lease renewal or move-out procedures.</p>
</div>
@endsection
