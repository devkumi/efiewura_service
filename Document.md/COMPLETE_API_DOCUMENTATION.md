# Complete API Documentation - Efiewura Property Management System

## Table of Contents
1. [Authentication Endpoints](#authentication-endpoints)
2. [Two-Factor Authentication Endpoints](#two-factor-authentication-endpoints)
3. [Admin Settings Endpoints](#admin-settings-endpoints)
4. [User Profile Endpoints](#user-profile-endpoints)
5. [Property Management Endpoints](#property-management-endpoints)
6. [Booking Management Endpoints](#booking-management-endpoints)
7. [Notification Endpoints](#notification-endpoints)
8. [Admin Management Endpoints](#admin-management-endpoints)

---

## Authentication Endpoints

### Register User
**POST** `/api/register`

Register a new user with role-specific profile creation.

#### Request Body
```json
{
  "firstname": "John",
  "lastname": "Doe",
  "email": "john.doe@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "landlord|tenant|admin",
  
  // Landlord-specific fields (required if role = "landlord")
  "business_name": "Doe Properties Ltd",
  "business_registration_number": "REG123456",
  "phone": "+233123456789",
  "address": "123 Main Street",
  "city": "Accra",
  "state": "Greater Accra",
  "postal_code": "GA123",
  "country": "Ghana",
  
  // Tenant-specific fields (required if role = "tenant")
  "phone": "+233123456789",
  "date_of_birth": "1990-01-01",
  "gender": "male|female|other",
  "occupation": "Software Engineer",
  "employer": "Tech Company",
  "monthly_income": 5000.00,
  "current_address": "456 Current Street",
  "emergency_contact_name": "Jane Doe",
  "emergency_contact_phone": "+233987654321",
  "emergency_contact_relationship": "Sister"
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
      "lastname": "Doe",
      "email": "john.doe@example.com",
      "role": "landlord",
      "created_at": "2025-08-03T10:00:00.000000Z",
      "landlord": {
        "id": 1,
        "business_name": "Doe Properties Ltd",
        "phone": "+233123456789",
        "status": "active",
        "verified": false
      }
    },
    "token": "1|abcdef123456...",
    "token_type": "Bearer"
  }
}
```

### Login User
**POST** `/api/login`

Authenticate user and return access token. Supports 2FA if enabled.

#### Request Body
```json
{
  "email": "john.doe@example.com",
  "password": "password123",
  
  // Optional: For 2FA-enabled accounts
  "two_factor_code": "123456",
  "recovery_code": "ABC123DEF456"
}
```

#### Response (Without 2FA)
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "firstname": "John",
      "lastname": "Doe",
      "email": "john.doe@example.com",
      "role": "landlord"
    },
    "token": "2|xyz789abc123...",
    "token_type": "Bearer"
  }
}
```

#### Response (2FA Required)
```json
{
  "success": false,
  "message": "Two-factor authentication required",
  "requires_2fa": true,
  "two_factor_method": "app"
}
```

### Logout User
**POST** `/api/logout`

Revoke the current access token.

**Headers:** `Authorization: Bearer {token}`

#### Response
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## Two-Factor Authentication Endpoints

### Generate 2FA Secret
**POST** `/api/2fa/generate-secret`

Generate a new 2FA secret and QR code for setup.

**Headers:** `Authorization: Bearer {token}`

#### Response
```json
{
  "success": true,
  "message": "2FA secret generated successfully",
  "data": {
    "secret": "ABCDEFGHIJKLMNOP",
    "qr_code_url": "otpauth://totp/Efiewura:user@example.com?secret=ABCDEFGHIJKLMNOP&issuer=Efiewura",
    "qr_code_image": "data:image/png;base64,iVBORw0KGgoAAAANSUhE...",
    "backup_codes": [
      "ABC123",
      "DEF456",
      "GHI789"
    ]
  }
}
```

### Confirm 2FA Setup
**POST** `/api/2fa/confirm`

Confirm 2FA setup by verifying a code from the authenticator app.

**Headers:** `Authorization: Bearer {token}`

#### Request Body
```json
{
  "code": "123456"
}
```

#### Response
```json
{
  "success": true,
  "message": "2FA has been enabled successfully",
  "data": {
    "recovery_codes": [
      "ABC123DEF456",
      "GHI789JKL012",
      "MNO345PQR678"
    ]
  }
}
```

### Verify 2FA Code
**POST** `/api/2fa/verify`

Verify a 2FA code (for testing purposes).

**Headers:** `Authorization: Bearer {token}`

#### Request Body
```json
{
  "code": "123456"
}
```

### Disable 2FA
**POST** `/api/2fa/disable`

Disable 2FA for the current user.

**Headers:** `Authorization: Bearer {token}`

#### Request Body
```json
{
  "password": "current_password"
}
```

#### Response
```json
{
  "success": true,
  "message": "2FA has been disabled"
}
```

### Get 2FA Status
**GET** `/api/2fa/status`

Get current 2FA status for the user.

**Headers:** `Authorization: Bearer {token}`

#### Response
```json
{
  "success": true,
  "data": {
    "two_factor_enabled": true,
    "two_factor_confirmed": true,
    "two_factor_required": false,
    "two_factor_method": "app",
    "recovery_codes_generated": true,
    "recovery_codes_remaining": 5
  }
}
```

### Get Recovery Codes
**GET** `/api/2fa/recovery-codes`

Get remaining recovery codes.

**Headers:** `Authorization: Bearer {token}`

#### Response
```json
{
  "success": true,
  "data": {
    "recovery_codes": [
      "ABC123DEF456",
      "GHI789JKL012",
      "MNO345PQR678"
    ],
    "codes_remaining": 3
  }
}
```

### Regenerate Recovery Codes
**POST** `/api/2fa/recovery-codes/regenerate`

Generate new recovery codes (invalidates old ones).

**Headers:** `Authorization: Bearer {token}`

#### Request Body
```json
{
  "password": "current_password"
}
```

#### Response
```json
{
  "success": true,
  "message": "Recovery codes regenerated successfully",
  "data": {
    "recovery_codes": [
      "NEW123CODE456",
      "NEW789CODE012",
      "NEW345CODE678"
    ]
  }
}
```

---

## Admin Settings Endpoints

**Note:** All admin settings endpoints require admin role authentication.

### Get All Settings
**GET** `/api/admin/settings`

Retrieve all system settings grouped by category.

**Headers:** `Authorization: Bearer {admin_token}`

#### Response
```json
{
  "success": true,
  "data": {
    "general": {
      "platform_name": "Efiewura Property Management",
      "platform_description": "Modern property rental management platform for Ghana",
      "support_email": "support@efiewura.com",
      "support_phone": "+233 123 456 789",
      "timezone": "Africa/Accra"
    },
    "users": {
      "allow_registration": true,
      "require_email_verification": true,
      "require_manual_approval": false,
      "password_min_length": 8,
      "session_timeout": 120,
      "require_special_chars": true
    },
    "properties": {
      "auto_approve": false,
      "max_images": 20,
      "max_file_size": 10,
      "commission_rate": 10.0
    },
    "payments": {
      "paypal": {"enabled": true},
      "stripe": {"enabled": true},
      "paystack": {"enabled": true},
      "mobile_money": {"enabled": true},
      "default_currency": "GHS",
      "payment_timeout": 30
    },
    "notifications": {
      "email": {
        "new_user": true,
        "new_property": true,
        "booking_confirm": true
      },
      "system": {
        "low_disk_space": true,
        "failed_payments": true
      }
    },
    "security": {
      "require_2fa": false,
      "max_login_attempts": 5,
      "lockout_duration": 30,
      "data_retention_days": 365
    }
  }
}
```

### Update All Settings
**PUT** `/api/admin/settings`

Update multiple settings across categories.

**Headers:** `Authorization: Bearer {admin_token}`

#### Request Body
```json
{
  "general": {
    "platform_name": "New Platform Name",
    "support_email": "newsupport@efiewura.com"
  },
  "security": {
    "require_2fa": true,
    "max_login_attempts": 3
  }
}
```

#### Response
```json
{
  "success": true,
  "message": "Settings updated successfully",
  "data": {
    // Updated settings object (same structure as GET response)
  }
}
```

### Reset Settings to Defaults
**POST** `/api/admin/settings/reset`

Reset all settings to their default values.

**Headers:** `Authorization: Bearer {admin_token}`

#### Response
```json
{
  "success": true,
  "message": "Settings reset to defaults successfully",
  "data": {
    // Default settings object
  }
}
```

### Get Category Settings
**GET** `/api/admin/settings/{category}`

Get settings for a specific category.

**Categories:** `general`, `users`, `properties`, `payments`, `notifications`, `security`

**Headers:** `Authorization: Bearer {admin_token}`

#### Response
```json
{
  "success": true,
  "data": {
    "general": {
      "platform_name": "Efiewura Property Management",
      "platform_description": "Modern property rental management platform for Ghana",
      "support_email": "support@efiewura.com",
      "support_phone": "+233 123 456 789",
      "timezone": "Africa/Accra"
    }
  }
}
```

### Update Category Settings
**PUT** `/api/admin/settings/{category}`

Update settings for a specific category.

**Headers:** `Authorization: Bearer {admin_token}`

#### Request Body
```json
{
  "platform_name": "Updated Platform Name",
  "support_email": "updated@efiewura.com"
}
```

#### Response
```json
{
  "success": true,
  "message": "General settings updated successfully",
  "data": {
    "general": {
      "platform_name": "Updated Platform Name",
      "support_email": "updated@efiewura.com",
      "platform_description": "Modern property rental management platform for Ghana",
      "support_phone": "+233 123 456 789",
      "timezone": "Africa/Accra"
    }
  }
}
```

---

## User Profile Endpoints

### Get User Profile
**GET** `/api/user`

Get the authenticated user's profile information.

**Headers:** `Authorization: Bearer {token}`

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "firstname": "John",
    "lastname": "Doe",
    "email": "john.doe@example.com",
    "role": "landlord",
    "preferences": {
      "timezone": "Africa/Lagos",
      "date_format": "DD/MM/YYYY",
      "theme": "light",
      "dashboard_refresh_interval": 30,
      "email_notifications": true,
      "sms_notifications": false,
      "new_bookings": true,
      "property_updates": true,
      "user_registrations": false,
      "system_alerts": true,
      "weekly_reports": false,
      "bio": "Property management professional"
    },
    "landlord": {
      "id": 1,
      "business_name": "Doe Properties Ltd",
      "phone": "+233123456789",
      "status": "active",
      "verified": true
    }
  }
}
```

### Update User Profile
**PUT** `/api/profile`

Update the authenticated user's profile information.

**Headers:** `Authorization: Bearer {token}`

#### Request Body
```json
{
  "firstname": "John",
  "lastname": "Doe",
  "email": "newemail@example.com",
  "bio": "Updated bio information",
  
  // Landlord-specific fields
  "business_name": "Updated Business Name",
  "phone": "+233987654321",
  
  // Preferences
  "preferences": {
    "timezone": "Africa/Accra",
    "theme": "dark",
    "notifications": {
      "email": true,
      "sms": false,
      "new_bookings": true
    }
  }
}
```

#### Response
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    // Updated user profile object
  }
}
```

### Change Password
**PUT** `/api/change-password`

Change the authenticated user's password.

**Headers:** `Authorization: Bearer {token}`

#### Request Body
```json
{
  "current_password": "old_password",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

#### Response
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

## Property Management Endpoints

### Get Properties
**GET** `/api/properties`

Get a list of properties with filtering and pagination.

#### Query Parameters
- `search` - Search in title and description
- `type` - Property type filter
- `status` - Property status filter
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `per_page` - Items per page (default: 15)
- `page` - Page number

#### Response
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Beautiful 3BR Apartment",
        "description": "Spacious apartment in prime location",
        "type": "apartment",
        "price": 1200.00,
        "status": "available",
        "location": "Accra, Ghana",
        "landlord": {
          "id": 1,
          "business_name": "Doe Properties Ltd"
        }
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

### Get Property Details
**GET** `/api/properties/{id}`

Get detailed information about a specific property.

#### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Beautiful 3BR Apartment",
    "description": "Spacious apartment in prime location",
    "type": "apartment",
    "price": 1200.00,
    "status": "available",
    "bedrooms": 3,
    "bathrooms": 2,
    "area": 120.5,
    "features": ["parking", "wifi", "ac"],
    "images": [
      "https://example.com/image1.jpg",
      "https://example.com/image2.jpg"
    ],
    "landlord": {
      "id": 1,
      "business_name": "Doe Properties Ltd",
      "phone": "+233123456789"
    }
  }
}
```

---

## Booking Management Endpoints

### Create Booking
**POST** `/api/bookings`

Create a new property booking.

**Headers:** `Authorization: Bearer {token}`

#### Request Body
```json
{
  "property_id": 1,
  "start_date": "2025-09-01",
  "end_date": "2025-09-30",
  "message": "I'm interested in this property"
}
```

#### Response
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 1,
    "property_id": 1,
    "tenant_id": 1,
    "start_date": "2025-09-01",
    "end_date": "2025-09-30",
    "status": "pending",
    "total_amount": 1200.00,
    "property": {
      "id": 1,
      "title": "Beautiful 3BR Apartment"
    }
  }
}
```

### Get User Bookings
**GET** `/api/bookings`

Get bookings for the authenticated user.

**Headers:** `Authorization: Bearer {token}`

#### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "property_id": 1,
      "start_date": "2025-09-01",
      "end_date": "2025-09-30",
      "status": "pending",
      "total_amount": 1200.00,
      "property": {
        "id": 1,
        "title": "Beautiful 3BR Apartment",
        "location": "Accra, Ghana"
      }
    }
  ]
}
```

---

## Error Responses

### Authentication Errors
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Validation Errors
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Server Errors
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Detailed error message"
}
```

---

## Authentication

Most endpoints require authentication using Bearer tokens. Include the token in the Authorization header:

```
Authorization: Bearer {your_access_token}
```

## Rate Limiting

API requests are rate-limited to prevent abuse. Default limits:
- 60 requests per minute for authenticated users
- 30 requests per minute for unauthenticated users

## Data Types

### Settings Data Types
- `string` - Text values
- `boolean` - true/false values
- `integer` - Whole numbers
- `float` - Decimal numbers
- `json` - Complex objects/arrays

### Date Formats
All dates are in ISO 8601 format: `YYYY-MM-DD` or `YYYY-MM-DDTHH:MM:SS.sssZ`

## Status Codes

- `200` - Success
- `201` - Created
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error
