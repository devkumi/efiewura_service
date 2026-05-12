# Landlord API Documentation - Efiewura Platform

## Overview

This documentation covers all API endpoints specifically designed for landlords in the Efiewura property rental management platform. Landlords can manage their properties, handle booking requests, configure settings, and monitor their rental business.

## Base URL & Authentication

### Base URL
```
http://localhost:8080/api
```

### Authentication
All landlord endpoints require authentication using Laravel Sanctum. Include the token in the Authorization header:
```
Authorization: Bearer {your-token}
```

### Required Role
All endpoints in this documentation require the **landlord** role.

---

## 1. User Management (Landlord Registration & Profile)

### Register as Landlord
**POST** `/register`

Register a new landlord account with business information.

#### Request
```json
{
    "firstname": "John",
    "lastname": "Property Owner",
    "email": "landlord@efiewura.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "landlord",
    "business_name": "John's Properties Ltd",
    "business_registration_number": "REG123456",
    "phone": "+233244123456",
    "address": "123 Main Street, Accra",
    "city": "Accra",
    "state": "Greater Accra",
    "postal_code": "GA123",
    "country": "Ghana"
}
```

#### Response
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 2,
            "firstname": "John",
            "lastname": "Property Owner",
            "email": "landlord@efiewura.com",
            "role": "landlord",
            "landlord": {
                "id": 1,
                "business_name": "John's Properties Ltd",
                "business_registration_number": "REG123456",
                "phone": "+233244123456",
                "address": "123 Main Street, Accra",
                "city": "Accra",
                "state": "Greater Accra",
                "country": "Ghana",
                "commission_rate": 10.00,
                "overdue_release_days": 30,
                "status": "active",
                "verified": false
            }
        },
        "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
    }
}
```

### Update Landlord Profile
**PUT** `/profile`

*Requires authentication with landlord role*

#### Request
```json
{
    "firstname": "John",
    "lastname": "Property Owner",
    "business_name": "Johns Premium Properties Ltd",
    "phone": "+233244123457",
    "address": "456 New Street, Accra",
    "city": "Accra",
    "state": "Greater Accra",
    "country": "Ghana"
}
```

#### Response
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "id": 2,
        "firstname": "John",
        "lastname": "Property Owner",
        "email": "landlord@efiewura.com",
        "role": "landlord",
        "landlord": {
            "business_name": "Johns Premium Properties Ltd",
            "phone": "+233244123457",
            "address": "456 New Street, Accra",
            "updated_at": "2025-06-30T17:00:00.000000Z"
        }
    }
}
```

---

## 2. Property Management

### Create Property
**POST** `/properties`

*Requires authentication with landlord role*

Create a new property listing.

#### Request
```json
{
    "title": "Luxury 3-Bedroom Villa",
    "description": "Spacious villa with garden and pool in prime location",
    "property_category_id": 2,
    "price": 5000.00,
    "currency": "GHS",
    "bedrooms": 3,
    "bathrooms": 3,
    "size_sqm": 200,
    "address": "Airport Residential Area",
    "city": "Accra",
    "state": "Greater Accra",
    "country": "Ghana",
    "furnished": true,
    "pets_allowed": true,
    "available_from": "2025-08-01",
    "lease_terms": "Minimum 12 months lease. Security deposit required.",
    "amenities": ["Pool", "Garden", "WiFi", "Parking", "Security", "Generator"],
    "images": ["image1.jpg", "image2.jpg"]
}
```

#### Response
```json
{
    "success": true,
    "message": "Property created successfully",
    "data": {
        "id": 2,
        "title": "Luxury 3-Bedroom Villa",
        "landlord_id": 1,
        "price": 5000.00,
        "currency": "GHS",
        "availability_status": "available",
        "is_active": true,
        "created_at": "2025-06-30T12:00:00.000000Z"
    }
}
```

### Update Property
**PUT** `/properties/{id}`

*Requires authentication with landlord role*

Update an existing property (only properties owned by the landlord).

#### Request
Same structure as create property, with optional fields.

#### Response
```json
{
    "success": true,
    "message": "Property updated successfully",
    "data": {
        "id": 2,
        "title": "Updated Villa Title",
        "price": 5500.00,
        "updated_at": "2025-06-30T16:00:00.000000Z"
    }
}
```

### Get My Properties
**GET** `/my-properties`

*Requires authentication with landlord role*

Retrieve all properties owned by the authenticated landlord.

#### Query Parameters
- `status` (optional) - Filter by availability status: available, occupied, maintenance
- `page` (optional) - Page number for pagination
- `per_page` (optional) - Items per page (default: 15)

#### Response
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "title": "Modern 2-Bedroom Apartment",
                "price": 2500.00,
                "currency": "GHS",
                "availability_status": "occupied",
                "active_bookings_count": 1,
                "total_views": 45,
                "is_active": true,
                "created_at": "2025-06-30T10:00:00.000000Z"
            },
            {
                "id": 2,
                "title": "Luxury 3-Bedroom Villa",
                "price": 5000.00,
                "currency": "GHS",
                "availability_status": "available",
                "active_bookings_count": 0,
                "total_views": 12,
                "is_active": true,
                "created_at": "2025-06-30T12:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 2
    }
}
```

### Update Property Status
**PATCH** `/properties/{id}/status`

*Requires authentication with landlord role*

Update the availability status of a property.

#### Request
```json
{
    "status": "maintenance"
}
```

#### Available Statuses
- `available` - Property is available for rent
- `occupied` - Property is currently rented
- `maintenance` - Property is under maintenance
- `inactive` - Property is temporarily inactive

#### Response
```json
{
    "success": true,
    "message": "Property status updated successfully",
    "data": {
        "id": 2,
        "availability_status": "maintenance",
        "updated_at": "2025-06-30T18:00:00.000000Z"
    }
}
```

### Delete Property
**DELETE** `/properties/{id}`

*Requires authentication with landlord role*

Soft delete a property (can be restored by admin).

#### Response
```json
{
    "success": true,
    "message": "Property deleted successfully"
}
```

---

## 3. Booking Management

### Get Landlord Bookings
**GET** `/bookings`

*Requires authentication with landlord role*

Retrieve all booking requests for properties owned by the landlord.

#### Query Parameters
- `status` (optional) - Filter by status: pending, confirmed, cancelled, rejected
- `property_id` (optional) - Filter by specific property
- `date_from` (optional) - Filter by booking date (YYYY-MM-DD)
- `date_to` (optional) - Filter by booking date (YYYY-MM-DD)
- `page` (optional) - Page number

#### Response
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "property": {
                    "id": 1,
                    "title": "Modern 2-Bedroom Apartment",
                    "address": "East Legon, Accra"
                },
                "tenant": {
                    "name": "Jane Smith",
                    "email": "jane@example.com",
                    "phone": "+233244654321"
                },
                "status": "pending",
                "move_in_date": "2025-08-01",
                "move_out_date": "2026-08-01",
                "lease_duration_months": 12,
                "monthly_rent": 2500.00,
                "security_deposit": 2500.00,
                "total_amount": 5000.00,
                "currency": "GHS",
                "occupation": "Software Engineer",
                "monthly_income": 6000.00,
                "created_at": "2025-06-30T14:00:00.000000Z"
            }
        ],
        "total_bookings": 5,
        "pending_bookings": 2,
        "confirmed_bookings": 2,
        "rejected_bookings": 1
    }
}
```

### Get Single Booking
**GET** `/bookings/{id}`

*Requires authentication with landlord role*

Get detailed information about a specific booking.

#### Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "property": {
            "id": 1,
            "title": "Modern 2-Bedroom Apartment",
            "address": "East Legon, Accra",
            "price": 2500.00,
            "images": ["https://example.com/image1.jpg"]
        },
        "status": "pending",
        "move_in_date": "2025-08-01",
        "move_out_date": "2026-08-01",
        "lease_duration_months": 12,
        "monthly_rent": 2500.00,
        "security_deposit": 2500.00,
        "total_amount": 5000.00,
        "currency": "GHS",
        "tenant_name": "Jane Smith",
        "tenant_phone": "+233244654321",
        "tenant_email": "jane@example.com",
        "tenant_message": "I'm interested in renting this property for 12 months.",
        "occupation": "Software Engineer",
        "employer": "Tech Company Ltd",
        "monthly_income": 6000.00,
        "emergency_contact": "John Smith (+233244111222)",
        "created_at": "2025-06-30T14:00:00.000000Z"
    }
}
```

### Confirm Booking
**PATCH** `/bookings/{id}/confirm`

*Requires authentication with landlord role*

Approve a pending booking request.

#### Response
```json
{
    "success": true,
    "message": "Booking confirmed successfully",
    "data": {
        "id": 1,
        "status": "confirmed",
        "confirmed_at": "2025-06-30T15:00:00.000000Z",
        "updated_at": "2025-06-30T15:00:00.000000Z"
    }
}
```

### Reject Booking
**PATCH** `/bookings/{id}/reject`

*Requires authentication with landlord role*

Reject a pending booking request.

#### Request (Optional)
```json
{
    "reason": "Property no longer available for the requested dates"
}
```

#### Response
```json
{
    "success": true,
    "message": "Booking rejected successfully",
    "data": {
        "id": 1,
        "status": "rejected",
        "rejection_reason": "Property no longer available for the requested dates",
        "rejected_at": "2025-06-30T16:00:00.000000Z",
        "updated_at": "2025-06-30T16:00:00.000000Z"
    }
}
```

---

## 4. Landlord Settings

### Get Landlord Settings
**GET** `/landlord/settings`

*Requires authentication with landlord role*

Retrieve current landlord settings and business information.

#### Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "business_name": "John's Properties Ltd",
        "business_registration_number": "REG123456",
        "phone": "+233244123456",
        "address": "123 Main Street, Accra",
        "city": "Accra",
        "state": "Greater Accra",
        "country": "Ghana",
        "commission_rate": 10.00,
        "overdue_release_days": 30,
        "status": "active",
        "verified": true,
        "verified_at": "2025-06-15T10:00:00.000000Z",
        "created_at": "2025-06-01T09:00:00.000000Z"
    }
}
```

### Update Landlord Settings
**PATCH** `/landlord/settings`

*Requires authentication with landlord role*

Update landlord-specific settings.

#### Request
```json
{
    "overdue_release_days": 14,
    "business_name": "Johns Premium Properties Ltd",
    "phone": "+233244123457",
    "address": "456 Updated Street, Accra"
}
```

#### Response
```json
{
    "success": true,
    "message": "Settings updated successfully",
    "data": {
        "id": 1,
        "business_name": "Johns Premium Properties Ltd",
        "phone": "+233244123457",
        "address": "456 Updated Street, Accra",
        "overdue_release_days": 14,
        "updated_at": "2025-06-30T17:00:00.000000Z"
    }
}
```

#### Settings Description
- **overdue_release_days**: Number of days after lease expiry before automatic property release
- **business_name**: Display name for your rental business
- **commission_rate**: Platform commission rate (read-only, set by admin)
- **verified**: Account verification status (read-only, updated by admin)

---

## 5. Notifications

### Get Notifications
**GET** `/notifications`

*Requires authentication with landlord role*

Retrieve notifications related to your properties and bookings.

#### Query Parameters
- `type` (optional) - Filter by notification type
- `read` (optional) - Filter by read status: true/false
- `page` (optional) - Page number

#### Response
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "type": "booking_request",
                "title": "New Booking Request",
                "message": "Jane Smith has requested to book your property 'Modern 2-Bedroom Apartment'",
                "data": {
                    "booking_id": 1,
                    "property_id": 1,
                    "tenant_name": "Jane Smith"
                },
                "is_read": false,
                "created_at": "2025-06-30T14:00:00.000000Z"
            }
        ],
        "unread_count": 5,
        "total": 15
    }
}
```

### Mark Notification as Read
**PATCH** `/notifications/{id}/read`

*Requires authentication*

#### Response
```json
{
    "success": true,
    "message": "Notification marked as read"
}
```

---

## 6. Error Handling

### Common HTTP Status Codes

#### 200 OK
Request successful

#### 201 Created
Resource created successfully

#### 400 Bad Request
```json
{
    "success": false,
    "message": "Invalid request data"
}
```

#### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

#### 403 Forbidden
```json
{
    "success": false,
    "message": "This action is unauthorized."
}
```

#### 404 Not Found
```json
{
    "success": false,
    "message": "Resource not found"
}
```

#### 422 Validation Error
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "price": [
            "The price must be a number."
        ]
    }
}
```

#### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Internal server error"
}
```

---

## 7. Rate Limiting

API requests are subject to rate limiting:
- **60 requests per minute** per authenticated user
- Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1625097600
```

---

## 8. Best Practices

### 1. Authentication
- Always include the Bearer token in request headers
- Handle token expiration gracefully
- Store tokens securely

### 2. Error Handling
- Always check the `success` field in responses
- Handle validation errors by displaying field-specific messages
- Implement retry logic for network failures

### 3. Pagination
- Use pagination for list endpoints to improve performance
- Check `last_page` to determine if more data is available

### 4. Property Management
- Update property status appropriately (available/occupied/maintenance)
- Include comprehensive property descriptions and images
- Set realistic pricing and lease terms

### 5. Booking Management
- Respond to booking requests promptly
- Provide clear rejection reasons when declining requests
- Keep tenant communication professional

---

## 9. Notification Types for Landlords

- **booking_request** - New booking request received
- **booking_cancelled** - Tenant cancelled confirmed booking
- **payment_received** - Payment received for booking
- **lease_expiry_reminder** - Property lease expiring soon
- **property_inquiry** - General inquiry about property
- **system_announcement** - Platform updates and announcements

This documentation covers all landlord-specific functionality in the Efiewura platform. For general platform features and tenant-specific endpoints, refer to the main API documentation.