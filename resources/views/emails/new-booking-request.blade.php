@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">{{ $notification->title }}</h2>

<div class="message-content">
    <p>Hello {{ $landlord->user->name }},</p>
    
    <p>{{ $notification->message }}</p>
    
    <p>A potential tenant is interested in your property and has submitted a detailed booking application. Please review the details below and respond promptly.</p>
</div>

<div class="details-box">
    <h3>Property Information</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $booking->property->title }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Location:</span>
        <span class="detail-value">{{ $booking->property->address }}, {{ $booking->property->city }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Monthly Rent:</span>
        <span class="detail-value">{{ $booking->property->currency }} {{ number_format($booking->monthly_rent, 2) }}</span>
    </div>
</div>

<div class="details-box">
    <h3>Tenant Information</h3>
    <div class="detail-item">
        <span class="detail-label">Name:</span>
        <span class="detail-value">{{ $booking->tenant_name }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Email:</span>
        <span class="detail-value">{{ $booking->tenant_email }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Phone:</span>
        <span class="detail-value">{{ $booking->tenant_phone }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Occupation:</span>
        <span class="detail-value">{{ $booking->occupation }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Employer:</span>
        <span class="detail-value">{{ $booking->employer }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Monthly Income:</span>
        <span class="detail-value">GHS {{ number_format($booking->monthly_income, 2) }}</span>
    </div>
</div>

<div class="details-box">
    <h3>Booking Details</h3>
    <div class="detail-item">
        <span class="detail-label">Move-in Date:</span>
        <span class="detail-value">{{ $booking->move_in_date->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Lease Duration:</span>
        <span class="detail-value">{{ $booking->lease_duration_months }} months</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Security Deposit:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->security_deposit, 2) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Total Amount:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span>
    </div>
</div>

@if($booking->tenant_message)
<div class="details-box">
    <h3>Message from Tenant</h3>
    <p style="font-style: italic; color: #4a5568;">"{{ $booking->tenant_message }}"</p>
</div>
@endif

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/bookings/{{ $booking->id }}" class="cta-button">
        Review & Respond to Booking
    </a>
</div>

<div class="message-content">
    <p><strong>Quick Actions:</strong></p>
    <ul style="margin: 15px 0; padding-left: 20px;">
        <li>Review the tenant's application and income verification</li>
        <li>Contact the tenant directly if you need more information</li>
        <li>Confirm or decline the booking request</li>
        <li>If confirmed, coordinate move-in details with the tenant</li>
    </ul>
    
    <p><em>Remember: Quick responses lead to higher booking success rates!</em></p>
</div>
@endsection
