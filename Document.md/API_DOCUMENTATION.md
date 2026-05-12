# Efiewura Property Management API Documentation

## Table of Contents
1. [Overview](#overview)
2. [Base URL & Authentication](#base-url--authentication)
3. [User Management](#user-management)
4. [Property Management](#property-management)
5. [Booking Management](#booking-management)
6. [Notification System](#notification-system)
7. [Time-Based Notifications](#time-based-notifications)
8. [Landlord Settings](#landlord-settings)
9. [Super Admin Portal](#super-admin-portal)
10. [Admin Dashboard API](#admin-dashboard-api)
11. [Admin Analytics API](#admin-analytics-api)
12. [Admin User Management API](#admin-user-management-api)
13. [Admin Property Management API](#admin-property-management-api)
14. [API Flow Diagrams](#api-flow-diagrams)
15. [Error Handling](#error-handling)

## Overview

Efiewura is a comprehensive property rental management platform that connects landlords and tenants. The API provides endpoints for property listings, booking management, automated notifications, and time-based reminders for lease management.

### Key Features
- 🏠 Property listing and management
- 📅 Booking system with approval workflow
- 🔔 Real-time notification system
- ⏰ Automated time-based reminders (lease expiry, payment due, move-in/out)
- 🏢 Landlord-specific settings and auto-release functionality
- 📧 Email notifications with HTML templates
- 👥 Role-based access control (Admin, Landlord, Tenant)
- 🗂️ Comprehensive soft delete system - no permanent data loss
- 📋 Full audit trails for compliance and data recovery
- 🔄 Reversible deletions with admin restore capabilities

## Base URL & Authentication

### Base URL
```
http://localhost:8080/api
```

### Authentication
This API uses Laravel Sanctum for authentication. Include the token in the Authorization header:
```
Authorization: Bearer {your-token}
```

### Available Roles
- **admin** - System administrators with full access
- **landlord** - Property owners who list and manage properties
- **tenant** - Users who browse and book properties
- **user** - General users with basic access

## User Management

### Register User
**POST** `/register`

Register a new user with role-specific additional fields.

#### Basic Registration (All Users)
```json
{
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "landlord"
}
```

#### Landlord Registration (Additional Fields)
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

#### Tenant Registration (Additional Fields)
```json
{
    "firstname": "Jane",
    "lastname": "Tenant",
    "email": "tenant@efiewura.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "tenant",
    "phone": "+233244654321",
    "date_of_birth": "1990-01-15",
    "gender": "female",
    "occupation": "Software Engineer",
    "employer": "Tech Company Ltd",
    "monthly_income": 5000.00,
    "current_address": "456 Current Street, Accra",
    "emergency_contact_name": "John Doe",
    "emergency_contact_phone": "+233244111222",
    "emergency_contact_relationship": "Brother"
}
```

#### Response
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "firstname": "John",
            "lastname": "Property Owner",
            "name": "John Property Owner",
            "email": "landlord@efiewura.com",
            "role": "landlord",
            "email_verified_at": null,
            "created_at": "2025-06-30T19:35:00.000000Z",
            "updated_at": "2025-06-30T19:35:00.000000Z",
            "landlord": {
                "id": 1,
                "user_id": 1,
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
                "verified": false,
                "created_at": "2025-06-30T19:35:00.000000Z"
            }
        },
        "token": "1|abcdef123456...",
        "token_type": "Bearer"
    }
}
```

### Login
**POST** `/login`

#### Request
```json
{
    "email": "landlord@efiewura.com",
    "password": "password123"
}
```

#### Response
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "firstname": "John",
            "lastname": "Property Owner", 
            "name": "John Property Owner",
            "email": "landlord@efiewura.com",
            "role": "landlord",
            "landlord": {
                "business_name": "John's Properties Ltd",
                "phone": "+233244123456",
                "overdue_release_days": 30
            }
        },
        "token": "2|xyz789abc456...",
        "token_type": "Bearer"
    }
}
```

### Get User Profile
**GET** `/profile`

*Requires authentication*

#### Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "firstname": "John",
        "lastname": "Property Owner",
        "name": "John Property Owner", 
        "email": "landlord@efiewura.com",
        "role": "landlord",
        "landlord": {
            "business_name": "John's Properties Ltd",
            "phone": "+233244123456",
            "overdue_release_days": 30,
            "status": "active",
            "verified": false
        }
    }
}
```

### Update Profile
**PUT** `/profile`

*Requires authentication*

Update the authenticated user's profile information. This endpoint supports updating basic user information as well as role-specific profile fields.

#### Request Examples

##### Basic User Information Update
```json
{
    "firstname": "John",
    "lastname": "Updated",
    "email": "newemail@example.com"
}
```

##### Landlord Profile Update
```json
{
    "firstname": "John",
    "lastname": "Property Owner",
    "email": "johnupdated@example.com",
    "business_name": "Updated Properties Ltd",
    "business_registration_number": "REG999999",
    "phone": "+233244999999",
    "address": "Updated Business Address",
    "city": "Accra",
    "state": "Greater Accra Region",
    "country": "Ghana",
    "postal_code": "GA999"
}
```

##### Tenant Profile Update
```json
{
    "firstname": "Jane",
    "lastname": "Updated Tenant",
    "email": "janeupdated@example.com",
    "phone": "+233244888888",
    "date_of_birth": "1992-05-20",
    "gender": "female",
    "occupation": "Senior Software Engineer",
    "employer": "Tech Solutions Ltd",
    "monthly_income": 7500.00,
    "current_address": "Updated Current Address",
    "emergency_contact_name": "Updated Emergency Contact",
    "emergency_contact_phone": "+233244777777",
    "emergency_contact_relationship": "Brother"
}
```

#### Response
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "firstname": "John",
        "lastname": "Property Owner",
        "name": "John Property Owner",
        "email": "johnupdated@example.com",
        "role": "landlord",
        "email_verified_at": "2025-06-30T19:35:00.000000Z",
        "created_at": "2025-06-30T19:35:00.000000Z",
        "updated_at": "2025-08-02T20:30:00.000000Z",
        "landlord": {
            "id": 1,
            "user_id": 1,
            "business_name": "Updated Properties Ltd",
            "business_registration_number": "REG999999",
            "phone": "+233244999999",
            "address": "Updated Business Address",
            "city": "Accra",
            "state": "Greater Accra Region",
            "country": "Ghana",
            "postal_code": "GA999",
            "commission_rate": 10.00,
            "overdue_release_days": 30,
            "status": "active",
            "verified": false,
            "created_at": "2025-06-30T19:35:00.000000Z",
            "updated_at": "2025-08-02T20:30:00.000000Z"
        }
    }
}
```

#### Error Response (Validation)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "date_of_birth": ["The date of birth must be a date before today."],
        "monthly_income": ["The monthly income must be a number."]
    }
}
```

#### Validation Rules
- **firstname**: Optional, string, max 255 characters
- **lastname**: Optional, string, max 255 characters  
- **email**: Optional, valid email, must be unique
- **business_name**: Optional (landlords), string, max 255 characters
- **business_registration_number**: Optional (landlords), string, max 50 characters
- **phone**: Optional, string, max 20 characters
- **address**: Optional (landlords), string, max 500 characters
- **city**: Optional, string, max 100 characters
- **state**: Optional, string, max 100 characters
- **country**: Optional, string, max 100 characters
- **postal_code**: Optional (landlords), string, max 20 characters
- **date_of_birth**: Optional (tenants), date, must be before today
- **gender**: Optional (tenants), must be: male, female, or other
- **occupation**: Optional (tenants), string, max 255 characters
- **employer**: Optional (tenants), string, max 255 characters
- **monthly_income**: Optional (tenants), numeric, minimum 0
- **current_address**: Optional (tenants), string, max 500 characters
- **emergency_contact_name**: Optional (tenants), string, max 255 characters
- **emergency_contact_phone**: Optional (tenants), string, max 20 characters
- **emergency_contact_relationship**: Optional (tenants), string, max 100 characters

### Change Password
**PUT** `/change-password`

*Requires authentication*

Change the authenticated user's password.

#### Request
```json
{
    "current_password": "oldpassword123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### Response
```json
{
    "success": true,
    "message": "Password changed successfully"
}
```

#### Error Response (Wrong Current Password)
```json
{
    "success": false,
    "message": "Current password is incorrect",
    "errors": {
        "current_password": ["The current password is incorrect."]
    }
}
```

#### Error Response (Validation)
```json
{
    "success": false,
    "message": "The password field confirmation does not match.",
    "errors": {
        "password": ["The password field confirmation does not match."]
    }
}
```

### Logout
**POST** `/logout`

*Requires authentication*

Revokes the current access token.

#### Response
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

## Property Management

### Get All Properties (Public)
**GET** `/properties`

Browse all available properties without authentication.

#### Query Parameters
- `category_id` (optional) - Filter by property category
- `city` (optional) - Filter by city
- `min_price` (optional) - Minimum price filter
- `max_price` (optional) - Maximum price filter
- `bedrooms` (optional) - Number of bedrooms
- `furnished` (optional) - true/false for furnished properties
- `page` (optional) - Page number for pagination

#### Example Request
```
GET /api/properties?city=Accra&bedrooms=2&furnished=true&page=1
```

#### Response
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "title": "Modern 2-Bedroom Apartment",
                "description": "Beautiful apartment in East Legon",
                "property_category": {
                    "id": 1,
                    "name": "Apartment"
                },
                "price": 2500.00,
                "currency": "GHS",
                "bedrooms": 2,
                "bathrooms": 2,
                "size_sqm": 85,
                "address": "East Legon, Accra",
                "city": "Accra",
                "state": "Greater Accra",
                "country": "Ghana",
                "furnished": true,
                "pets_allowed": false,
                "available_from": "2025-07-01",
                "availability_status": "available",
                "images": [
                    "https://example.com/image1.jpg",
                    "https://example.com/image2.jpg"
                ],
                "amenities": ["WiFi", "Parking", "Security", "Generator"],
                "landlord": {
                    "business_name": "John's Properties Ltd",
                    "phone": "+233244123456"
                },
                "created_at": "2025-06-30T10:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 68
    }
}
```

### Get Property Categories
**GET** `/property-categories`

Get list of available property categories.

#### Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Apartment",
            "description": "Multi-story residential building with individual units",
            "is_active": true
        },
        {
            "id": 2,
            "name": "House",
            "description": "Single-family detached house",
            "is_active": true
        },
        {
            "id": 3,
            "name": "Studio",
            "description": "Single room apartment with kitchenette",
            "is_active": true
        }
    ]
}
```

### Get Single Property
**GET** `/properties/{id}`

Get detailed information about a specific property.

#### Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Modern 2-Bedroom Apartment",
        "description": "Beautiful apartment in East Legon with modern amenities...",
        "property_category": {
            "id": 1,
            "name": "Apartment"
        },
        "price": 2500.00,
        "currency": "GHS",
        "bedrooms": 2,
        "bathrooms": 2,
        "size_sqm": 85,
        "address": "East Legon, Accra",
        "city": "Accra",
        "state": "Greater Accra",
        "country": "Ghana",
        "furnished": true,
        "pets_allowed": false,
        "available_from": "2025-07-01",
        "availability_status": "available",
        "lease_terms": "Minimum 6 months lease required...",
        "images": [
            "https://example.com/image1.jpg"
        ],
        "amenities": ["WiFi", "Parking", "Security", "Generator"],
        "landlord": {
            "id": 1,
            "business_name": "John's Properties Ltd",
            "phone": "+233244123456",
            "verified": true
        },
        "created_at": "2025-06-30T10:00:00.000000Z"
    }
}
```

### Create Property (Landlord Only)
**POST** `/properties`

*Requires authentication with landlord role*

#### Request
```json
{
    "title": "Luxury 3-Bedroom Villa",
    "description": "Spacious villa with garden and pool",
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
        "availability_status": "available",
        "is_active": true,
        "created_at": "2025-06-30T12:00:00.000000Z"
    }
}
```

### Update Property (Landlord Only)
**PUT** `/properties/{id}`

*Requires authentication with landlord role*

#### Request
Same structure as create property, with optional fields.

### Get My Properties (Landlord Only)
**GET** `/my-properties`

*Requires authentication with landlord role*

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
                "created_at": "2025-06-30T10:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 2,
        "total": 8
    }
}
```

## Booking Management

### Browse Available Properties
**GET** `/browse-available-properties`

*Requires authentication*

Get properties available for booking with enhanced filtering.

#### Response
Similar to GET `/properties` but only shows available properties.

### Create Booking Request
**POST** `/bookings`

*Requires authentication*

Submit a booking request for a property.

#### Request
```json
{
    "property_id": 1,
    "move_in_date": "2025-08-01",
    "lease_duration_months": 12,
    "tenant_name": "Jane Smith",
    "tenant_phone": "+233244654321",
    "tenant_email": "jane@example.com",
    "tenant_message": "I'm interested in renting this property for 12 months.",
    "occupation": "Software Engineer",
    "employer": "Tech Company Ltd",
    "monthly_income": 6000.00,
    "emergency_contact": "John Smith (+233244111222)"
}
```

#### Response
```json
{
    "success": true,
    "message": "Booking request submitted successfully",
    "data": {
        "id": 1,
        "property_id": 1,
        "status": "pending",
        "move_in_date": "2025-08-01",
        "move_out_date": "2026-08-01",
        "lease_duration_months": 12,
        "monthly_rent": 2500.00,
        "security_deposit": 2500.00,
        "total_amount": 5000.00,
        "currency": "GHS",
        "tenant_name": "Jane Smith",
        "tenant_email": "jane@example.com",
        "created_at": "2025-06-30T14:00:00.000000Z"
    }
}
```

### Get My Bookings
**GET** `/my-bookings`

*Requires authentication*

Get current user's bookings.

#### Query Parameters
- `status` (optional) - Filter by status: pending, confirmed, cancelled, rejected
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
                    "address": "East Legon, Accra",
                    "images": ["https://example.com/image1.jpg"]
                },
                "status": "confirmed",
                "move_in_date": "2025-08-01",
                "move_out_date": "2026-08-01",
                "monthly_rent": 2500.00,
                "currency": "GHS",
                "confirmed_at": "2025-06-30T15:00:00.000000Z",
                "created_at": "2025-06-30T14:00:00.000000Z"
            }
        ],
        "total_bookings": 3,
        "pending_bookings": 1,
        "confirmed_bookings": 1,
        "cancelled_bookings": 1
    }
}
```

### Get Single Booking
**GET** `/bookings/{id}`

*Requires authentication*

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
            "landlord": {
                "business_name": "John's Properties Ltd",
                "phone": "+233244123456"
            }
        },
        "status": "confirmed",
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
        "occupation": "Software Engineer",
        "monthly_income": 6000.00,
        "confirmed_at": "2025-06-30T15:00:00.000000Z",
        "created_at": "2025-06-30T14:00:00.000000Z"
    }
}
```

### Confirm Booking (Landlord Only)
**PATCH** `/bookings/{id}/confirm`

*Requires authentication with landlord role*

#### Response
```json
{
    "success": true,
    "message": "Booking confirmed successfully",
    "data": {
        "id": 1,
        "status": "confirmed",
        "confirmed_at": "2025-06-30T15:00:00.000000Z"
    }
}
```

### Reject Booking (Landlord Only)
**PATCH** `/bookings/{id}/reject`

*Requires authentication with landlord role*

#### Request (Optional)
```json
{
    "reason": "Property no longer available"
}
```

#### Response
```json
{
    "success": true,
    "message": "Booking rejected",
    "data": {
        "id": 1,
        "status": "rejected",
        "rejected_at": "2025-06-30T15:30:00.000000Z"
    }
}
```

### Cancel Booking
**PATCH** `/bookings/{id}/cancel`

*Requires authentication (booking owner only)*

#### Request (Optional)
```json
{
    "reason": "Change of plans"
}
```

#### Response
```json
{
    "success": true,
    "message": "Booking cancelled successfully",
    "data": {
        "id": 1,
        "status": "cancelled",
        "cancelled_at": "2025-06-30T16:00:00.000000Z"
    }
}
```

## Notification System

### Get Notifications
**GET** `/notifications`

*Requires authentication*

#### Query Parameters
- `type` (optional) - Filter by notification type
- `read` (optional) - Filter by read status (true/false)
- `page` (optional) - Page number
- `per_page` (optional) - Items per page (max 50)

#### Response
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "type": "booking_confirmed",
                "title": "Booking Confirmed!",
                "message": "Your booking for Modern 2-Bedroom Apartment has been confirmed.",
                "data": {
                    "booking_id": 1,
                    "property_title": "Modern 2-Bedroom Apartment",
                    "move_in_date": "2025-08-01"
                },
                "priority": "high",
                "read": false,
                "read_at": null,
                "email_sent": true,
                "email_sent_at": "2025-06-30T15:00:05.000000Z",
                "created_at": "2025-06-30T15:00:00.000000Z"
            },
            {
                "id": 2,
                "type": "lease_expiry_reminder",
                "title": "Urgent: Lease Expires in 30 Days",
                "message": "URGENT: Your lease expires in 30 days! Please arrange renewal or prepare for move-out.",
                "data": {
                    "booking_id": 1,
                    "property_title": "Modern 2-Bedroom Apartment",
                    "move_out_date": "2026-08-01",
                    "reminder_days": 30,
                    "recipient_type": "tenant"
                },
                "priority": "urgent",
                "read": false,
                "email_sent": true,
                "created_at": "2025-07-02T09:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 3,
        "total": 12,
        "unread_count": 8
    }
}
```

### Get Notification Counts
**GET** `/notifications/counts`

*Requires authentication*

#### Response
```json
{
    "success": true,
    "data": {
        "total": 25,
        "unread": 8,
        "by_type": {
            "booking_confirmed": 3,
            "booking_rejected": 1,
            "lease_expiry_reminder": 2,
            "payment_reminder_5_days": 1,
            "move_in_reminder": 1
        },
        "by_priority": {
            "low": 5,
            "medium": 12,
            "high": 6,
            "urgent": 2
        }
    }
}
```

### Get Notification Types
**GET** `/notifications/types`

*Requires authentication*

#### Response
```json
{
    "success": true,
    "data": {
        "booking_confirmed": {
            "name": "Booking Confirmed",
            "description": "Your booking request has been approved by the landlord",
            "category": "booking",
            "typical_priority": "high"
        },
        "lease_expiry_reminder": {
            "name": "Lease Expiry Reminder",
            "description": "Reminder about upcoming lease expiration",
            "category": "lease_management",
            "typical_priority": "urgent"
        },
        "payment_reminder_5_days": {
            "name": "Payment Reminder (5 Days)",
            "description": "Rent payment is due in 5 days",
            "category": "payment",
            "typical_priority": "high"
        }
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
    "message": "Notification marked as read",
    "data": {
        "id": 1,
        "read": true,
        "read_at": "2025-06-30T16:30:00.000000Z"
    }
}
```

### Mark All Notifications as Read
**PATCH** `/notifications/mark-all-read`

*Requires authentication*

#### Response
```json
{
    "success": true,
    "message": "All notifications marked as read",
    "data": {
        "marked_count": 8
    }
}
```

### Resend Email Notification
**POST** `/notifications/{id}/resend-email`

*Requires authentication*

#### Response
```json
{
    "success": true,
    "message": "Email notification resent successfully",
    "data": {
        "email_sent_at": "2025-06-30T16:45:00.000000Z"
    }
}
```

## Time-Based Notifications

The system automatically sends time-based notifications for lease management:

### Lease Expiry Reminders
- **60 days before expiry** - Early reminder
- **30 days before expiry** - Urgent reminder  
- **7 days before expiry** - Final notice

### Payment Reminders
- **5 days before due date** - Advance reminder
- **On due date** - Payment due today
- **3 days after due date** - Overdue notice

### Move-In/Move-Out Reminders
- **7 days before move-in** - Preparation reminder
- **On move-in date** - Welcome message
- **7 days before move-out** - Move-out preparation

### Auto-Release Feature
- Properties are automatically released back to market when rent is overdue
- Configurable by landlord (7-365 days)
- Both tenant and landlord are notified

### Automated Email Templates
All time-based notifications include:
- Professional HTML email templates
- Property and booking details
- Clear call-to-action buttons
- Landlord contact information

## Landlord Settings

### Get Landlord Settings
**GET** `/landlord/settings`

*Requires authentication with landlord role*

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

#### Request
```json
{
    "overdue_release_days": 14,
    "business_name": "Johns Premium Properties Ltd",
    "phone": "+233244123457"
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
        "overdue_release_days": 14,
        "updated_at": "2025-06-30T17:00:00.000000Z"
    }
}
```

## Super Admin Portal

### Overview

The Super Admin Portal provides comprehensive platform management capabilities exclusively for system administrators. All admin endpoints require authentication with admin role.

**Base Path**: `/admin/*`  
**Authentication**: Required (admin role only)  
**Authorization**: `Authorization: Bearer {admin-token}`

### Get Admin Dashboard
**GET** `/admin/dashboard`

*Requires authentication with admin role*

Comprehensive system overview with real-time metrics and analytics.

#### Response
```json
{
    "success": true,
    "message": "Admin dashboard data retrieved successfully",
    "data": {
        "users": {
            "total": 8,
            "admins": 1,
            "landlords": 3,
            "tenants": 4,
            "regular_users": 0,
            "recent_registrations": 8,
            "verified_landlords": 0,
            "verified_tenants": 0
        },
        "properties": {
            "total": 3,
            "available": 3,
            "occupied": 0,
            "under_maintenance": 0,
            "inactive": 0,
            "recent_listings": 3
        },
        "bookings": {
            "total": 2,
            "pending": 2,
            "confirmed": 0,
            "cancelled": 0,
            "rejected": 0,
            "completed": 0,
            "recent_bookings": 2
        },
        "notifications": {
            "total": 11,
            "unread": 11,
            "email_sent": 8,
            "email_pending": 3,
            "recent_notifications": 11
        },
        "revenue": {
            "total_bookings_value": 0,
            "monthly_revenue": 0,
            "average_booking_value": 0
        },
        "system": {
            "total_categories": 7,
            "active_categories": 7,
            "database_size": "0.16 MB",
            "system_health": "good"
        }
    }
}
```

### User Management

#### Get All Users
**GET** `/admin/users`

*Requires authentication with admin role*

Retrieve all users with advanced filtering and pagination.

##### Query Parameters
- `role` (optional) - Filter by user role: admin, landlord, tenant, user
- `search` (optional) - Search in name and email fields
- `verified` (optional) - Filter by verification status: true/false
- `status` (optional) - Filter by user status:
  - `active` (default) - Show only active users
  - `deleted` - Show only soft-deleted users (admin only)
  - `all` - Show all users including deleted ones
- `date_from` (optional) - Filter by registration date (YYYY-MM-DD)
- `date_to` (optional) - Filter by registration date (YYYY-MM-DD)
- `sort_by` (optional) - Sort field (default: created_at)
- `sort_order` (optional) - Sort order: asc/desc (default: desc)
- `per_page` (optional) - Items per page (default: 15)

##### Example Request
```
GET /api/admin/users?role=landlord&verified=true&per_page=10
```

##### Response
```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "data": {
        "data": [
            {
                "id": 2,
                "name": "John Property Owner",
                "email": "landlord1@test.com",
                "role": "landlord",
                "created_at": "2025-07-02T19:58:01.000000Z",
                "landlord": {
                    "id": 1,
                    "business_name": "Premium Properties Ltd",
                    "phone": "+233244123456",
                    "verified": false,
                    "status": "active"
                }
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 3
    }
}
```

#### Get User Details
**GET** `/admin/users/{id}`

*Requires authentication with admin role*

##### Response
```json
{
    "success": true,
    "message": "User details retrieved successfully",
    "data": {
        "id": 2,
        "name": "John Property Owner",
        "email": "landlord1@test.com",
        "role": "landlord",
        "created_at": "2025-07-02T19:58:01.000000Z",
        "landlord": {
            "business_name": "Premium Properties Ltd",
            "verified": false,
            "status": "active"
        },
        "bookings": [
            {
                "id": 1,
                "status": "pending",
                "property": {
                    "title": "Modern 2-Bedroom Apartment"
                }
            }
        ],
        "notifications": [
            {
                "id": 1,
                "type": "welcome_message",
                "read_at": null
            }
        ]
    }
}
```

#### Update User
**PATCH** `/admin/users/{id}`

*Requires authentication with admin role*

##### Request
```json
{
    "firstname": "Updated First",
    "lastname": "Updated Last",
    "email": "newemail@example.com",
    "role": "landlord",
    "verified": true,
    "status": "active"
}
```

##### Response
```json
{
    "success": true,
    "message": "User updated successfully",
    "data": {
        "id": 2,
        "firstname": "Updated First",
        "lastname": "Updated Last",
        "name": "Updated First Updated Last",
        "email": "newemail@example.com",
        "role": "landlord",
        "updated_at": "2025-07-02T20:00:00.000000Z"
    }
}
```

#### Delete User
**DELETE** `/admin/users/{id}`

*Requires authentication with admin role*

##### Response
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

### Property Management

#### Get All Properties
**GET** `/admin/properties`

*Requires authentication with admin role*

##### Query Parameters
- `status` (optional) - Filter by availability status
- `city` (optional) - Filter by city
- `category_id` (optional) - Filter by property category
- `landlord_id` (optional) - Filter by landlord
- `search` (optional) - Search in title, description, address
- `price_min` (optional) - Minimum price filter
- `price_max` (optional) - Maximum price filter
- `active` (optional) - Filter by active status: true/false
- `deleted_status` (optional) - Filter by deletion status:
  - `active` (default) - Show only active properties
  - `deleted` - Show only soft-deleted properties (admin only)
  - `all` - Show all properties including deleted ones
- `sort_by` (optional) - Sort field (default: created_at)
- `sort_order` (optional) - Sort order: asc/desc (default: desc)
- `per_page` (optional) - Items per page (default: 15)

##### Response
```json
{
    "success": true,
    "message": "Properties retrieved successfully",
    "data": {
        "data": [
            {
                "id": 1,
                "title": "Modern 2-Bedroom Apartment in East Legon",
                "price": 2500.00,
                "currency": "GHS",
                "availability_status": "available",
                "city": "Accra",
                "state": "Greater Accra",
                "is_active": true,
                "admin_notes": null,
                "landlord": {
                    "user": {
                        "name": "John Property Owner"
                    }
                },
                "category": {
                    "name": "Apartment"
                },
                "bookings_count": 1
            }
        ],
        "total": 3
    }
}
```

#### Update Property
**PATCH** `/admin/properties/{id}`

*Requires authentication with admin role*

##### Request
```json
{
    "availability_status": "under_maintenance",
    "is_active": false,
    "admin_notes": "Property under review for compliance issues"
}
```

##### Response
```json
{
    "success": true,
    "message": "Property updated successfully",
    "data": {
        "id": 1,
        "availability_status": "under_maintenance",
        "is_active": false,
        "admin_notes": "Property under review for compliance issues",
        "updated_at": "2025-07-02T20:05:00.000000Z"
    }
}
```

### Booking Management

#### Get All Bookings
**GET** `/admin/bookings`

*Requires authentication with admin role*

##### Query Parameters
- `status` (optional) - Filter by booking status
- `user_id` (optional) - Filter by tenant user ID
- `landlord_id` (optional) - Filter by landlord ID
- `property_id` (optional) - Filter by property ID
- `date_from` (optional) - Filter by creation date
- `date_to` (optional) - Filter by creation date
- `amount_min` (optional) - Minimum booking amount
- `amount_max` (optional) - Maximum booking amount
- `deleted_status` (optional) - Filter by deletion status:
  - `active` (default) - Show only active bookings
  - `deleted` - Show only soft-deleted bookings (admin only)
  - `all` - Show all bookings including deleted ones
- `sort_by` (optional) - Sort field (default: created_at)
- `sort_order` (optional) - Sort order: asc/desc (default: desc)
- `per_page` (optional) - Items per page (default: 15)

##### Response
```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": {
        "data": [
            {
                "id": 1,
                "status": "pending",
                "move_in_date": "2025-08-01T00:00:00.000000Z",
                "lease_duration_months": 12,
                "monthly_rent": 2500.00,
                "total_amount": 5000.00,
                "currency": "GHS",
                "tenant_name": "Sarah Johnson",
                "tenant_email": "tenant1@test.com",
                "admin_notes": null,
                "property": {
                    "title": "Modern 2-Bedroom Apartment",
                    "city": "Accra"
                },
                "user": {
                    "name": "Sarah Johnson"
                },
                "landlord": {
                    "user": {
                        "name": "John Property Owner"
                    }
                }
            }
        ],
        "total": 2
    }
}
```

#### Update Booking
**PATCH** `/admin/bookings/{id}`

*Requires authentication with admin role*

##### Request
```json
{
    "status": "confirmed",
    "admin_notes": "Booking approved after verification"
}
```

##### Response
```json
{
    "success": true,
    "message": "Booking updated successfully",
    "data": {
        "id": 1,
        "status": "confirmed",
        "confirmed_at": "2025-07-02T20:10:00.000000Z",
        "admin_notes": "Booking approved after verification"
    }
}
```

### Notification Management

#### Get All Notifications
**GET** `/admin/notifications`

*Requires authentication with admin role*

##### Query Parameters
- `type` (optional) - Filter by notification type
- `priority` (optional) - Filter by priority: low, medium, high
- `read_status` (optional) - Filter by read status: read/unread
- `email_status` (optional) - Filter by email sent status: true/false
- `user_id` (optional) - Filter by user ID
- `date_from` (optional) - Filter by creation date
- `date_to` (optional) - Filter by creation date
- `sort_by` (optional) - Sort field (default: created_at)
- `sort_order` (optional) - Sort order: asc/desc (default: desc)
- `per_page` (optional) - Items per page (default: 15)

##### Response
```json
{
    "success": true,
    "message": "Notifications retrieved successfully",
    "data": {
        "data": [
            {
                "id": 1,
                "type": "system_announcement",
                "title": "System Maintenance Notice",
                "message": "The system will undergo scheduled maintenance...",
                "priority": "medium",
                "read_at": null,
                "email_sent": true,
                "email_sent_at": "2025-07-02T20:00:00.000000Z",
                "user": {
                    "name": "John Property Owner"
                }
            }
        ],
        "total": 11
    }
}
```

#### Create System Notification
**POST** `/admin/notifications`

*Requires authentication with admin role*

##### Request
```json
{
    "type": "system_announcement",
    "title": "Platform Update Notice",
    "message": "We've released new features to improve your experience. Check out the latest updates in your dashboard.",
    "priority": "medium",
    "target_users": [2, 3, 4, 5],
    "send_email": true,
    "data": {
        "update_version": "1.2.0",
        "features": ["Enhanced search", "Mobile improvements", "Bug fixes"]
    }
}
```

##### Response
```json
{
    "success": true,
    "message": "Notifications created successfully",
    "data": {
        "total_sent": 4,
        "email_sent": true,
        "notifications": [
            {
                "id": 15,
                "type": "system_announcement",
                "title": "Platform Update Notice"
            }
        ]
    }
}
```

#### Mark Admin Notification as Read
**PATCH** `/admin/notifications/{id}/read`

*Requires authentication with admin role*

Mark a single notification as read in the admin portal.

##### Response
```json
{
    "success": true,
    "message": "Notification marked as read successfully",
    "data": {
        "id": 22,
        "user_id": 13,
        "type": "test",
        "title": "Test Admin Notification",
        "message": "This is a test notification for the admin portal",
        "data": null,
        "property_id": null,
        "booking_id": null,
        "triggered_by_user_id": null,
        "priority": "medium",
        "is_read": false,
        "read_at": "2025-07-29T22:01:39.000000Z",
        "read_by": 13,
        "email_sent": false,
        "sms_sent": false,
        "push_sent": false,
        "scheduled_for": null,
        "is_sent": true,
        "created_at": "2025-07-29T21:58:30.000000Z",
        "updated_at": "2025-07-29T21:58:31.000000Z"
    }
}
```

#### Mark Multiple Admin Notifications as Read
**PATCH** `/admin/notifications/read-multiple`

*Requires authentication with admin role*

Mark multiple notifications as read in a single request.

##### Request
```json
{
    "notification_ids": [24, 25, 26]
}
```

##### Response
```json
{
    "success": true,
    "message": "Successfully marked 3 notifications as read",
    "data": {
        "updated_count": 3,
        "notification_ids": [24, 25, 26]
    }
}
```

#### Soft Delete Admin Notification
**DELETE** `/admin/notifications/{id}`

*Requires authentication with admin role*

Soft delete a notification from the system. The notification will be marked as deleted but retained in the database for audit purposes. Only admins can view deleted notifications.

##### Response
```json
{
    "success": true,
    "message": "Notification deleted successfully",
    "data": {
        "id": 32,
        "status": "deleted",
        "deleted_by": 15,
        "deleted_at": "2025-07-29T22:16:36.000000Z"
    }
}
```

#### Restore Deleted Notification
**PATCH** `/admin/notifications/{id}/restore`

*Requires authentication with admin role*

Restore a previously soft-deleted notification back to active status.

##### Response
```json
{
    "success": true,
    "message": "Notification restored successfully",
    "data": {
        "id": 34,
        "status": "active",
        "restored_at": "2025-07-29T22:17:18.473141Z"
    }
}
```

### Soft Delete Management

#### Soft Delete User
**DELETE** `/admin/users/{id}`

*Requires authentication with admin role*

Soft delete a user from the system. The user will be marked as deleted but retained in the database for audit purposes. Soft deleted users cannot log in but their data is preserved for compliance and recovery.

##### Response
```json
{
    "success": true,
    "message": "User soft deleted successfully",
    "data": {
        "id": 25,
        "status": "deleted",
        "deleted_by": 1,
        "deleted_at": "2025-07-29T22:37:18.000000Z"
    }
}
```

#### Restore Deleted User
**PATCH** `/admin/users/{id}/restore`

*Requires authentication with admin role*

Restore a previously soft-deleted user back to active status.

##### Response
```json
{
    "success": true,
    "message": "User restored successfully",
    "data": {
        "id": 25,
        "status": "active",
        "restored_at": "2025-07-29T22:40:00.000000Z"
    }
}
```

#### Soft Delete Property
**DELETE** `/admin/properties/{id}`

*Requires authentication with admin role*

Soft delete a property from the system. The property will be marked as deleted but retained in the database for audit purposes. Soft deleted properties are automatically hidden from public listings but their booking history is preserved.

##### Response
```json
{
    "success": true,
    "message": "Property soft deleted successfully",
    "data": {
        "id": 10,
        "status": "deleted",
        "is_active": false,
        "deleted_by": 1,
        "deleted_at": "2025-07-29T22:37:18.000000Z"
    }
}
```

#### Restore Deleted Property
**PATCH** `/admin/properties/{id}/restore`

*Requires authentication with admin role*

Restore a previously soft-deleted property back to active status.

##### Response
```json
{
    "success": true,
    "message": "Property restored successfully",
    "data": {
        "id": 10,
        "status": "active",
        "is_active": true,
        "restored_at": "2025-07-29T22:40:00.000000Z"
    }
}
```

#### Soft Delete Booking
**DELETE** `/admin/bookings/{id}`

*Requires authentication with admin role*

Soft delete a booking from the system. The booking will be marked as deleted but retained in the database for audit purposes. Soft deleted bookings are excluded from active listings but preserved for compliance and reporting.

##### Response
```json
{
    "success": true,
    "message": "Booking soft deleted successfully",
    "data": {
        "id": 8,
        "status": "deleted",
        "deleted_by": 1,
        "deleted_at": "2025-07-29T22:37:18.000000Z"
    }
}
```

#### Restore Deleted Booking
**PATCH** `/admin/bookings/{id}/restore`

*Requires authentication with admin role*

Restore a previously soft-deleted booking back to pending status.

##### Response
```json
{
    "success": true,
    "message": "Booking restored successfully",
    "data": {
        "id": 8,
        "status": "pending",
        "restored_at": "2025-07-29T22:40:00.000000Z"
    }
}
```

#### View Notifications by Status
**GET** `/admin/notifications`

*Requires authentication with admin role*

The admin notifications endpoint now supports status filtering:

##### Query Parameters
- `status` (optional) - Filter by notification status:
  - `active` (default) - Show only active notifications
  - `deleted` - Show only soft-deleted notifications (admin only)
  - `all` - Show all notifications including deleted ones

##### Example Requests
```
GET /api/admin/notifications?status=active     # Default behavior
GET /api/admin/notifications?status=deleted    # View deleted notifications
GET /api/admin/notifications?status=all        # View all notifications
```

### Analytics

#### Get Analytics Data
**GET** `/admin/analytics`

*Requires authentication with admin role*

##### Query Parameters
- `period` (optional) - Analysis period in days: 7, 30, 90 (default: 30)

##### Example Request
```
GET /api/admin/analytics?period=30
```

##### Response
```json
{
    "success": true,
    "message": "Analytics data retrieved successfully",
    "data": {
        "user_registrations": {
            "total": 8,
            "by_role": {
                "admin": 1,
                "landlord": 3,
                "tenant": 4
            },
            "daily": {
                "2025-07-02": 8
            }
        },
        "property_listings": {
            "total": 3,
            "by_status": {
                "available": 3
            },
            "daily": {
                "2025-07-02": 3
            }
        },
        "booking_trends": {
            "total": 2,
            "by_status": {
                "pending": 2
            },
            "daily": {
                "2025-07-02": 2
            }
        },
        "revenue_analytics": {
            "total_value": 0,
            "by_status": {},
            "daily": {}
        },
        "popular_locations": {
            "Accra": 2,
            "Tema": 1
        },
        "popular_categories": {
            "Apartment": 2,
            "House": 1
        },
        "landlord_performance": [
            {
                "landlord_id": 1,
                "name": "John Property Owner",
                "business_name": "Premium Properties Ltd",
                "properties_count": 1,
                "bookings_count": 1,
                "verified": false
            }
        ]
    },
    "period": "30 days"
}
```

### System Management

#### Get System Settings
**GET** `/admin/settings`

*Requires authentication with admin role*

##### Response
```json
{
    "success": true,
    "message": "System settings retrieved successfully",
    "data": {
        "platform": {
            "name": "Efiewura",
            "version": "1.0.0",
            "environment": "local",
            "debug_mode": true
        },
        "features": {
            "email_notifications": true,
            "auto_property_release": true,
            "booking_confirmations": true,
            "user_verification": true
        },
        "limits": {
            "max_properties_per_landlord": 100,
            "max_bookings_per_user": 10,
            "max_file_upload_size": "10MB",
            "max_images_per_property": 10
        },
        "email": {
            "driver": "log",
            "from_address": "hello@example.com",
            "from_name": "Laravel"
        },
        "database": {
            "driver": "sqlite",
            "size": "0.16 MB"
        }
    }
}
```

#### Get System Logs
**GET** `/admin/logs`

*Requires authentication with admin role*

##### Query Parameters
- `type` (optional) - Log type (default: application)
- `lines` (optional) - Number of lines to retrieve (default: 100)

##### Response
```json
{
    "success": true,
    "message": "System logs retrieved successfully",
    "data": {
        "log_type": "application",
        "lines_requested": 100,
        "lines_returned": 10,
        "logs": [
            "[2025-07-02 20:00:00] local.INFO: User login successful",
            "[2025-07-02 20:01:00] local.INFO: Property created successfully"
        ]
    }
}
```

#### Create System Backup
**POST** `/admin/backup`

*Requires authentication with admin role*

##### Request
```json
{
    "type": "full"
}
```

##### Response
```json
{
    "success": true,
    "message": "Backup feature not implemented yet",
    "data": {
        "backup_type": "full",
        "timestamp": "2025-07-02T20:15:00.000000Z"
    }
}
```

## API Flow Diagrams

### 1. User Registration & Property Listing Flow

```
┌─────────────┐    ┌──────────────┐    ┌─────────────────┐
│   Landlord  │───▶│ POST /register│───▶│  Create Profile │
│   Signup    │    │   (landlord)  │    │   & Get Token   │
└─────────────┘    └──────────────┘    └─────────────────┘
                           │                      │
                           ▼                      ▼
                   ┌──────────────┐    ┌─────────────────┐
                   │ POST /login  │    │ POST /properties│
                   │  Get Token   │    │  List Property  │
                   └──────────────┘    └─────────────────┘
```

### 2. Property Booking Flow

```
┌─────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Tenant    │───▶│ GET /properties  │───▶│  Browse & Find  │
│  Browse     │    │   (public)       │    │   Property      │
└─────────────┘    └──────────────────┘    └─────────────────┘
                           │                        │
                           ▼                        ▼
                   ┌──────────────────┐    ┌─────────────────┐
                   │ POST /register   │    │ POST /bookings  │
                   │   (tenant)       │    │ Submit Request  │
                   └──────────────────┘    └─────────────────┘
                           │                        │
                           ▼                        ▼
                   ┌──────────────────┐    ┌─────────────────┐
                   │   Notification   │    │   Landlord      │
                   │   to Landlord    │    │   Reviews       │
                   └──────────────────┘    └─────────────────┘
                                                   │
                                                   ▼
                                           ┌─────────────────┐
                                           │ PATCH /bookings │
                                           │ /confirm or     │
                                           │ /reject         │
                                           └─────────────────┘
```

### 3. Time-Based Notification Flow

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│ Daily Scheduler │───▶│ Check Due Dates  │───▶│ Find Bookings   │
│   (9:00 AM)     │    │    Command       │    │ Needing Alerts  │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                               │                        │
                               ▼                        ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │  Lease Expiry    │    │ Payment Due     │
                       │   Reminders      │    │   Reminders     │
                       └──────────────────┘    └─────────────────┘
                               │                        │
                               ▼                        ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │  Create In-App   │    │ Send Email      │
                       │  Notifications   │    │ Notifications   │
                       └──────────────────┘    └─────────────────┘
                               │                        │
                               ▼                        ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │ Auto-Release     │    │  Move-In/Out    │
                       │ Overdue Props    │    │   Reminders     │
                       └──────────────────┘    └─────────────────┘
```

### 4. Notification Management Flow

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│  User Logs In   │───▶│ GET /notifications│───▶│ Display Unread  │
│                 │    │     /counts      │    │    Count        │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                               │                        │
                               ▼                        ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │ GET /notifications│    │   View All      │
                       │                  │    │ Notifications   │
                       └──────────────────┘    └─────────────────┘
                               │                        │
                               ▼                        ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │ PATCH /notifications│  │ POST /notifications│
                       │    /{id}/read    │    │  /{id}/resend   │
                       └──────────────────┘    └─────────────────┘
```

## Error Handling

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field must be at least 8 characters."],
        "move_in_date": ["The move in date must be a date after today."]
    }
}
```

### Authentication Error (401)
```json
{
    "success": false,
    "message": "Unauthenticated",
    "error": "Token not provided or invalid"
}
```

### Authorization Error (403)
```json
{
    "success": false,
    "message": "Access denied. Insufficient privileges.",
    "error": "This action requires landlord role"
}
```

### Resource Not Found (404)
```json
{
    "success": false,
    "message": "Resource not found",
    "error": "Property with ID 999 does not exist"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error",
    "error": "An unexpected error occurred"
}
```

## Testing the API

### Example cURL Commands

#### Register a Landlord
```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "John",
    "lastname": "Property Owner",
    "email": "landlord@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "landlord",
    "business_name": "Johns Properties Ltd",
    "phone": "+233244123456",
    "city": "Accra"
  }'
```

#### Login and Get Token
```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "landlord@test.com",
    "password": "password123"
  }'
```

#### Create a Property (replace TOKEN)
```bash
curl -X POST http://localhost:8080/api/properties \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "title": "Beautiful 2-Bedroom Apartment",
    "description": "Modern apartment in great location",
    "property_category_id": 1,
    "price": 2500.00,
    "currency": "GHS",
    "bedrooms": 2,
    "bathrooms": 2,
    "address": "East Legon, Accra",
    "city": "Accra",
    "furnished": true,
    "available_from": "2025-08-01"
  }'
```

#### Update Profile (replace TOKEN)
```bash
curl -X PUT http://localhost:8080/api/profile \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "firstname": "Updated First",
    "lastname": "Updated Last",
    "business_name": "Updated Business Name",
    "phone": "+233244999999"
  }'
```

#### Submit a Booking Request
```bash
curl -X POST http://localhost:8080/api/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TENANT_TOKEN" \
  -d '{
    "property_id": 1,
    "move_in_date": "2025-08-01",
    "lease_duration_months": 12,
    "tenant_name": "Jane Smith",
    "tenant_phone": "+233244654321",
    "tenant_email": "jane@example.com",
    "monthly_income": 6000.00
  }'
```

#### Get Notifications
```bash
curl -X GET http://localhost:8080/api/notifications \
  -H "Authorization: Bearer TOKEN"
```

#### Update Landlord Settings
```bash
curl -X PATCH http://localhost:8080/api/landlord/settings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer LANDLORD_TOKEN" \
  -d '{
    "overdue_release_days": 14
  }'
```

#### Admin Notification Management
```bash
# Get all admin notifications with filtering
curl -X GET "http://localhost:8080/api/admin/notifications?type=system_announcement&priority=high" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Mark a notification as read
curl -X PATCH http://localhost:8080/api/admin/notifications/22/read \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Mark multiple notifications as read
curl -X PATCH http://localhost:8080/api/admin/notifications/read-multiple \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -d '{
    "notification_ids": [24, 25, 26]
  }'

# Create system-wide notification
curl -X POST http://localhost:8080/api/admin/notifications \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -d '{
    "type": "system_announcement",
    "title": "Platform Update Notice",
    "message": "New features have been released!",
    "priority": "medium",
    "target_users": [2, 3, 4, 5],
    "send_email": true
  }'

# Soft delete a notification
curl -X DELETE http://localhost:8080/api/admin/notifications/22 \
  -H "Authorization: Bearer ADMIN_TOKEN"

# View deleted notifications (admin only)
curl -X GET "http://localhost:8080/api/admin/notifications?status=deleted" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Restore a deleted notification
curl -X PATCH http://localhost:8080/api/admin/notifications/22/restore \
  -H "Authorization: Bearer ADMIN_TOKEN"

# View all notifications (active and deleted)
curl -X GET "http://localhost:8080/api/admin/notifications?status=all" \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

#### Admin User Management with Soft Delete
```bash
# View all users including deleted ones
curl -X GET "http://localhost:8080/api/admin/users?status=all" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# View only soft-deleted users
curl -X GET "http://localhost:8080/api/admin/users?status=deleted" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Soft delete a user
curl -X DELETE http://localhost:8080/api/admin/users/25 \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Restore a soft-deleted user
curl -X PATCH http://localhost:8080/api/admin/users/25/restore \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

#### Admin Property Management with Soft Delete
```bash
# View all properties including deleted ones
curl -X GET "http://localhost:8080/api/admin/properties?deleted_status=all" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# View only soft-deleted properties
curl -X GET "http://localhost:8080/api/admin/properties?deleted_status=deleted" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Soft delete a property
curl -X DELETE http://localhost:8080/api/admin/properties/10 \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Restore a soft-deleted property
curl -X PATCH http://localhost:8080/api/admin/properties/10/restore \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

#### Admin Booking Management with Soft Delete
```bash
# View all bookings including deleted ones
curl -X GET "http://localhost:8080/api/admin/bookings?deleted_status=all" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# View only soft-deleted bookings
curl -X GET "http://localhost:8080/api/admin/bookings?deleted_status=deleted" \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Soft delete a booking
curl -X DELETE http://localhost:8080/api/admin/bookings/8 \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Restore a soft-deleted booking
curl -X PATCH http://localhost:8080/api/admin/bookings/8/restore \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

## Admin Dashboard API

### Dashboard Overview
Get comprehensive dashboard data for admin interface.

**Endpoint:** `GET /admin/dashboard`

**Response:**
```json
{
    "success": true,
    "data": {
        "users": {
            "total": 12,
            "active": 10,
            "inactive": 2,
            "recent": [
                {
                    "id": 1,
                    "firstname": "John",
                    "lastname": "Doe",
                    "email": "john@example.com",
                    "role": "landlord",
                    "created_at": "2024-01-15T10:30:00Z"
                }
            ]
        },
        "properties": {
            "total": 25,
            "active": 20,
            "pending": 3,
            "rejected": 2,
            "recent": [
                {
                    "id": 1,
                    "title": "Modern Apartment",
                    "location": "Accra",
                    "price": 1500,
                    "status": "active",
                    "landlord": "John Doe"
                }
            ]
        },
        "bookings": {
            "total": 45,
            "pending": 8,
            "confirmed": 30,
            "cancelled": 7,
            "recent": [
                {
                    "id": 1,
                    "property": "Modern Apartment",
                    "tenant": "Jane Smith",
                    "status": "confirmed",
                    "amount": 1500,
                    "created_at": "2024-01-15T10:30:00Z"
                }
            ]
        },
        "notifications": {
            "total": 150,
            "unread": 12,
            "sent_today": 5
        },
        "system": {
            "server_status": "healthy",
            "database_status": "connected",
            "last_backup": "2024-01-15T02:00:00Z",
            "system_uptime": "15 days, 4 hours"
        }
    }
}
```

## Admin Analytics API

### Analytics Overview
Get platform analytics data.

**Endpoint:** `GET /admin/analytics`

**Response:**
```json
{
    "success": true,
    "data": {
        "overview": {
            "total_users": 125,
            "total_properties": 45,
            "total_bookings": 89,
            "total_revenue": 125000
        },
        "trends": {
            "user_growth": "12%",
            "property_growth": "8%",
            "booking_growth": "15%",
            "revenue_growth": "22%"
        },
        "quick_stats": {
            "active_users": 98,
            "active_properties": 38,
            "pending_bookings": 12,
            "monthly_revenue": 45000
        }
    }
}
```

### User Growth Analytics
Get user growth data for charts.

**Endpoint:** `GET /admin/analytics/user-growth`

**Query Parameters:**
- `period` (optional): `7d`, `30d`, `90d`, `1y` (default: `30d`)

**Response:**
```json
{
    "success": true,
    "data": {
        "labels": ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        "datasets": [
            {
                "label": "Total Users",
                "data": [10, 15, 25, 35, 50, 65],
                "borderColor": "rgb(75, 192, 192)",
                "tension": 0.1
            },
            {
                "label": "Landlords",
                "data": [3, 5, 8, 12, 18, 22],
                "borderColor": "rgb(255, 99, 132)",
                "tension": 0.1
            },
            {
                "label": "Tenants",
                "data": [7, 10, 17, 23, 32, 43],
                "borderColor": "rgb(54, 162, 235)",
                "tension": 0.1
            }
        ]
    }
}
```

### Booking Trends Analytics
Get booking trends data for charts.

**Endpoint:** `GET /admin/analytics/booking-trends`

**Response:**
```json
{
    "success": true,
    "data": {
        "labels": ["Week 1", "Week 2", "Week 3", "Week 4"],
        "datasets": [
            {
                "label": "Confirmed Bookings",
                "data": [12, 18, 15, 22],
                "backgroundColor": "rgba(75, 192, 192, 0.2)",
                "borderColor": "rgba(75, 192, 192, 1)"
            },
            {
                "label": "Pending Bookings",
                "data": [5, 8, 6, 10],
                "backgroundColor": "rgba(255, 206, 86, 0.2)",
                "borderColor": "rgba(255, 206, 86, 1)"
            },
            {
                "label": "Cancelled Bookings",
                "data": [2, 3, 4, 1],
                "backgroundColor": "rgba(255, 99, 132, 0.2)",
                "borderColor": "rgba(255, 99, 132, 1)"
            }
        ]
    }
}
```

### Revenue Analytics
Get revenue analytics data.

**Endpoint:** `GET /admin/analytics/revenue`

**Response:**
```json
{
    "success": true,
    "data": {
        "total_revenue": 125000,
        "monthly_revenue": 45000,
        "average_booking_value": 1800,
        "revenue_by_month": {
            "labels": ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            "data": [15000, 18000, 22000, 25000, 20000, 45000]
        },
        "top_performing_properties": [
            {
                "id": 1,
                "title": "Luxury Villa",
                "revenue": 25000,
                "bookings": 15
            }
        ]
    }
}
```

## Admin User Management API

### Enhanced User Listing
Get users with advanced filtering and admin data.

**Endpoint:** `GET /admin/users`

**Query Parameters:**
- `role`: Filter by role (`admin`, `landlord`, `tenant`)
- `status`: Filter by status (`active`, `inactive`, `banned`, `suspended`)
- `search`: Search by name or email
- `sort`: Sort by field (`created_at`, `firstname`, `email`)
- `direction`: Sort direction (`asc`, `desc`)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "firstname": "John",
            "lastname": "Doe",
            "email": "john@example.com",
            "role": "landlord",
            "status": "active",
            "email_verified_at": "2024-01-15T10:30:00Z",
            "created_at": "2024-01-15T10:30:00Z",
            "properties_count": 3,
            "bookings_count": 12,
            "last_login": "2024-01-20T14:22:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "per_page": 20,
        "total": 95
    }
}
```

### User Profile Data
Get detailed user profile data for admin view.

**Endpoint:** `GET /admin/users/{id}/profile`

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "firstname": "John",
            "lastname": "Doe",
            "email": "john@example.com",
            "role": "landlord",
            "status": "active"
        },
        "profile": {
            "phone": "+233123456789",
            "address": "123 Main St, Accra",
            "date_of_birth": "1985-06-15",
            "identification": "GHA-123456789",
            "bank_details": {
                "account_name": "John Doe",
                "account_number": "1234567890",
                "bank_name": "GCB Bank"
            }
        },
        "activity": {
            "properties_owned": 3,
            "total_bookings": 25,
            "total_revenue": 45000,
            "join_date": "2024-01-15",
            "last_activity": "2024-01-20T14:22:00Z"
        }
    }
}
```

### User Actions
Perform administrative actions on user accounts.

**Endpoint:** `POST /admin/users/{id}/actions`

**Request Body:**
```json
{
    "action": "suspend",  // suspend, activate, ban, verify_email, reset_password
    "reason": "Violation of terms of service",
    "duration": 30  // For suspensions, days
}
```

**Response:**
```json
{
    "success": true,
    "message": "User suspended successfully",
    "data": {
        "user_id": 1,
        "action": "suspend",
        "reason": "Violation of terms of service",
        "duration": 30,
        "performed_by": "admin@efiewura.com",
        "performed_at": "2024-01-20T15:30:00Z"
    }
}
```

## Admin Property Management API

### Property Moderation
Moderate property listings with approve/reject workflow.

**Endpoint:** `POST /admin/properties/{id}/moderate`

**Request Body:**
```json
{
    "action": "approve",  // approve, reject, flag, unflag, feature
    "reason": "Property meets all requirements",
    "notes": "Excellent property with good documentation"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Property approved successfully",
    "data": {
        "property_id": 1,
        "action": "approve",
        "reason": "Property meets all requirements",
        "notes": "Excellent property with good documentation",
        "moderated_by": "admin@efiewura.com",
        "moderated_at": "2024-01-20T15:30:00Z",
        "previous_status": "pending",
        "new_status": "active"
    }
}
```

### Enhanced Property Listing
Get properties with admin moderation data.

**Endpoint:** `GET /admin/properties`

**Query Parameters:**
- `status`: Filter by status (`active`, `pending`, `rejected`, `flagged`)
- `featured`: Filter featured properties (`true`, `false`)
- `landlord_id`: Filter by landlord
- `search`: Search by title or location

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Modern Apartment",
            "location": "Accra",
            "price": 1500,
            "status": "active",
            "featured": false,
            "landlord": {
                "id": 2,
                "firstname": "John",
                "lastname": "Doe",
                "email": "john@example.com"
            },
            "moderation": {
                "moderated_by": "admin@efiewura.com",
                "moderated_at": "2024-01-20T15:30:00Z",
                "moderation_notes": "Approved after verification"
            },
            "stats": {
                "views": 150,
                "inquiries": 12,
                "bookings": 3
            }
        }
    ]
}
```

### Settings Management
Get and update platform settings.

**Endpoint:** `GET /admin/settings`

**Response:**
```json
{
    "success": true,
    "data": {
        "general": {
            "site_name": "Efiewura",
            "site_description": "Property rental platform",
            "maintenance_mode": false,
            "registration_open": true
        },
        "notifications": {
            "email_notifications": true,
            "sms_notifications": false,
            "push_notifications": true,
            "notification_frequency": "immediate"
        },
        "payments": {
            "payment_gateway": "paystack",
            "commission_rate": 5,
            "auto_payout": true,
            "minimum_payout": 100
        },
        "security": {
            "two_factor_auth": false,
            "session_timeout": 60,
            "password_policy": "medium",
            "api_rate_limit": 60
        },
        "features": {
            "property_approval": true,
            "auto_booking_confirmation": false,
            "review_system": true,
            "messaging_system": true
        },
        "integrations": {
            "google_maps": true,
            "social_login": false,
            "analytics": true,
            "email_service": "smtp"
        }
    }
}
```

**Update Settings:**
**Endpoint:** `PUT /admin/settings`

**Request Body:**
```json
{
    "general": {
        "maintenance_mode": true
    },
    "payments": {
        "commission_rate": 6
    }
}
```

## Rate Limiting

The API implements rate limiting to prevent abuse:

- **Authentication endpoints**: 5 requests per minute
- **General API endpoints**: 60 requests per minute
- **Notification endpoints**: 30 requests per minute

When rate limit is exceeded:
```json
{
    "message": "Too Many Attempts.",
    "retry_after": 60
}
```

## Webhook Support (Future)

Planned webhook events:
- `booking.created`
- `booking.confirmed`
- `booking.cancelled`
- `payment.overdue`
- `lease.expiring`

## Support

For API support, please contact:
- Email: api-support@efiewura.com
- Documentation: https://docs.efiewura.com
- Status Page: https://status.efiewura.com
