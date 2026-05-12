@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">{{ $notification->title }}</h2>

<div class="message-content">
    <p>Welcome to Efiewura, {{ $user->name }}! 🎉</p>
    
    <p>{{ $notification->message }}</p>
    
    @if($user->role === 'landlord')
        <p>As a landlord, you can:</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>List your properties and reach thousands of potential tenants</li>
            <li>Manage booking requests efficiently</li>
            <li>Track property performance and analytics</li>
            <li>Communicate directly with interested tenants</li>
        </ul>
    @elseif($user->role === 'tenant')
        <p>As a tenant, you can:</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Browse thousands of verified properties</li>
            <li>Apply for properties with our simple booking system</li>
            <li>Communicate directly with landlords</li>
            <li>Track your booking applications</li>
        </ul>
    @endif
</div>

<div class="details-box">
    <h3>Your Account Details</h3>
    <div class="detail-item">
        <span class="detail-label">Name:</span>
        <span class="detail-value">{{ $user->name }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Email:</span>
        <span class="detail-value">{{ $user->email }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Role:</span>
        <span class="detail-value">{{ ucfirst($user->role) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Registration Date:</span>
        <span class="detail-value">{{ $user->created_at->format('F j, Y') }}</span>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/dashboard" class="cta-button">
        Get Started Now
    </a>
</div>

<div class="message-content">
    <p><strong>Need help getting started?</strong></p>
    <p>Our support team is here to help you every step of the way. Feel free to reach out if you have any questions.</p>
</div>
@endsection
