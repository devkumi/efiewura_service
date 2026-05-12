@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">Booking Request Update</h2>

<div class="message-content">
    <p>Hello {{ $booking->tenant_name }},</p>
    
    <p>{{ $notification->message }}</p>
    
    <p>While this particular application wasn't successful, we encourage you to continue your search. There are many other great properties available on our platform.</p>
</div>

<div class="details-box">
    <h3>Booking Request Details</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $booking->property->title }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Location:</span>
        <span class="detail-value">{{ $booking->property->address }}, {{ $booking->property->city }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Applied Date:</span>
        <span class="detail-value">{{ $booking->created_at->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Decision Date:</span>
        <span class="detail-value">{{ $booking->rejected_at->format('F j, Y') }}</span>
    </div>
    @if(isset($notification->data['reason']) && $notification->data['reason'])
    <div class="detail-item">
        <span class="detail-label">Reason:</span>
        <span class="detail-value">{{ $notification->data['reason'] }}</span>
    </div>
    @endif
</div>

<div class="details-box">
    <h3>Landlord Information</h3>
    <div class="detail-item">
        <span class="detail-label">Landlord:</span>
        <span class="detail-value">{{ $booking->landlord->user->name }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Business:</span>
        <span class="detail-value">{{ $booking->landlord->business_name }}</span>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/properties" class="cta-button">
        Browse Other Properties
    </a>
</div>

<div class="message-content">
    <p><strong>Don't get discouraged!</strong> Here are some tips for your next application:</p>
    
    <ul style="margin: 15px 0; padding-left: 20px;">
        <li><strong>Complete Your Profile:</strong> Ensure all your information is up-to-date</li>
        <li><strong>Provide References:</strong> Include work and personal references</li>
        <li><strong>Income Verification:</strong> Have recent pay stubs or bank statements ready</li>
        <li><strong>Quick Response:</strong> Apply quickly to popular properties</li>
        <li><strong>Personal Message:</strong> Include a brief, professional message with your application</li>
    </ul>
    
    <p><strong>Alternative Options:</strong></p>
    <ul style="margin: 15px 0; padding-left: 20px;">
        <li>Set up property alerts for new listings in your preferred areas</li>
        <li>Consider properties with similar features in nearby locations</li>
        <li>Adjust your search criteria to include more options</li>
        <li>Contact our support team for personalized assistance</li>
    </ul>
    
    <p>Remember, finding the right property takes time. The perfect home is waiting for you!</p>
</div>

<div style="background-color: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="color: #0ea5e9; margin-bottom: 10px;">💡 Pro Tip</h3>
    <p style="margin: 0; color: #0369a1;">Properties with multiple photos, detailed descriptions, and competitive pricing tend to get more applications. Use our advanced filters to find properties that match your specific needs and budget.</p>
</div>
@endsection
