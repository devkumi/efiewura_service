@extends('emails.layouts.base')

@section('content')
<div class="notification-type type-{{ $notification->priority }}">
    {{ ucfirst($notification->priority) }} Priority
</div>

<h2 class="message-title">🎯 {{ $notification->title }}</h2>

<div class="message-content">
    <p>Hello {{ $landlord->user->name }},</p>
    
    <p>{{ $notification->message }}</p>
    
    <p>Your property is attracting attention and generating interest from potential tenants. This is a great sign that your listing is performing well!</p>
</div>

<div class="details-box">
    <h3>Property Performance</h3>
    <div class="detail-item">
        <span class="detail-label">Property:</span>
        <span class="detail-value">{{ $property->title }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Location:</span>
        <span class="detail-value">{{ $property->address }}, {{ $property->city }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Total Views:</span>
        <span class="detail-value"><strong>{{ number_format($property->views_count) }} views</strong></span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Listed Date:</span>
        <span class="detail-value">{{ $property->created_at->format('F j, Y') }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Days Active:</span>
        <span class="detail-value">{{ $property->created_at->diffInDays(now()) }} days</span>
    </div>
</div>

<div class="details-box">
    <h3>Engagement Insights</h3>
    <div class="detail-item">
        <span class="detail-label">Average Views per Day:</span>
        <span class="detail-value">{{ number_format($property->views_count / max($property->created_at->diffInDays(now()), 1), 1) }}</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Milestone Reached:</span>
        <span class="detail-value">{{ $property->views_count }} views</span>
    </div>
    <div class="detail-item">
        <span class="detail-label">Next Milestone:</span>
        <span class="detail-value">{{ $property->views_count + (10 - ($property->views_count % 10)) }} views</span>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}/properties/{{ $property->id }}/analytics" class="cta-button">
        View Detailed Analytics
    </a>
</div>

<div class="message-content">
    <p><strong>Keep the momentum going!</strong> Here are some tips to maximize your property's visibility:</p>
    
    <ul style="margin: 15px 0; padding-left: 20px;">
        <li><strong>Update Photos:</strong> High-quality images increase engagement</li>
        <li><strong>Enhance Description:</strong> Detailed, accurate descriptions attract more viewers</li>
        <li><strong>Competitive Pricing:</strong> Ensure your price is competitive for the area</li>
        <li><strong>Quick Responses:</strong> Respond promptly to inquiries and booking requests</li>
        <li><strong>Property Features:</strong> Highlight unique amenities and features</li>
    </ul>
    
    <p><strong>Performance Benchmarks:</strong></p>
    <ul style="margin: 15px 0; padding-left: 20px;">
        <li>Properties with 10+ photos get 40% more views</li>
        <li>Detailed descriptions increase booking inquiries by 25%</li>
        <li>Competitive pricing leads to 60% faster bookings</li>
        <li>Quick response times improve tenant satisfaction by 80%</li>
    </ul>
</div>

@if($property->views_count >= 50)
<div style="background-color: #f0fdf4; border: 1px solid #16a34a; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="color: #16a34a; margin-bottom: 10px;">🌟 High Performance Alert!</h3>
    <p style="margin: 0; color: #15803d;">Your property is in the top tier of viewed listings! Consider this a great opportunity to optimize your listing further or adjust pricing if you're receiving multiple inquiries.</p>
</div>
@elseif($property->views_count >= 20)
<div style="background-color: #fffbeb; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="color: #f59e0b; margin-bottom: 10px;">📈 Good Performance</h3>
    <p style="margin: 0; color: #d97706;">Your property is performing well! Continue with your current strategy and consider small optimizations to boost performance even further.</p>
</div>
@endif

<div class="message-content">
    <p>Thank you for choosing Efiewura to list your property. We're committed to helping you succeed!</p>
</div>
@endsection
