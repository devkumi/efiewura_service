@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">🎉 {{ $notification->title }}</h2>

<div class="message-content">
    <p>Hello {{ $booking->tenant_name }},</p>
    
    <p><strong>Great news!</strong> {{ $notification->message }}</p>
    
    <p>Your booking application has been approved by the landlord. Please review the details below and prepare for your move-in.</p>
</div>

<div class="details-box">
    <h3>Property Information</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $booking->property->title }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Address:</span>
        <span class="detail-value">{{ $booking->property->address }}, {{ $booking->property->city }}, {{ $booking->property->state }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Monthly Rent:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->monthly_rent, 2) }}</span>
    </div>
</div>

<div class="details-box">
    <h3>Landlord Contact Information</h3>
    <div class="detail-item">
        <span class="detail-label">Landlord:</span>
        <span class="detail-value">{{ $booking->landlord->user->name }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Business:</span>
        <span class="detail-value">{{ $booking->landlord->business_name }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Phone:</span>
        <span class="detail-value">{{ $booking->landlord->phone }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Email:</span>
        <span class="detail-value">{{ $booking->landlord->user->email }}</span>
    </div>
</div>

<div class="details-box">
    <h3>Move-in Details</h3>
    <div class="detail-item">
        <span class="detail-label">Move-in Date:</span>
        <span class="detail-value">{{ $booking->move_in_date->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Lease Duration:</span>
        <span class="detail-value">{{ $booking->lease_duration_months }} months</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Lease End Date:</span>
        <span class="detail-value">{{ $booking->move_out_date->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Confirmation Date:</span>
        <span class="detail-value">{{ $booking->confirmed_at->format('F j, Y \a\t g:i A') }}</span>
    </div>
</div>

<div class="details-box">
    <h3>Financial Summary</h3>
    <div class="detail-item">
        <span class="detail-label">Monthly Rent:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->monthly_rent, 2) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Security Deposit:</span>
        <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->security_deposit, 2) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Total Amount Due:</span>
        <span class="detail-value"><strong>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</strong></span>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/bookings/{{ $booking->id }}" class="cta-button">
        View Booking Details
    </a>
</div>

<div class="message-content">
    <p><strong>Next Steps:</strong></p>
    <ol style="margin: 15px 0; padding-left: 20px;">
        <li><strong>Contact Your Landlord:</strong> Reach out to coordinate move-in logistics</li>
        <li><strong>Prepare Payment:</strong> Arrange for the total amount due before move-in</li>
        <li><strong>Schedule Inspection:</strong> Plan a property walkthrough before moving in</li>
        <li><strong>Gather Documents:</strong> Prepare ID, income proof, and references if needed</li>
        <li><strong>Plan Your Move:</strong> Organize logistics for your move-in date</li>
    </ol>
    
    <p><strong>Important:</strong> Please maintain communication with your landlord to ensure a smooth move-in process.</p>
    
    <p>Congratulations on securing your new home! We're excited to be part of your journey.</p>
</div>
@endsection
