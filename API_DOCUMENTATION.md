# Efiewura API Documentation

**Base URL:** `http://localhost:8000/api`  
**Auth:** Laravel Sanctum — Bearer token in `Authorization: Bearer <token>` header  
**Format:** All requests/responses are JSON. All responses share a common envelope:

```json
{
  "success": true,
  "message": "Human-readable message",
  "data": {}
}
```

**Status Codes:** `200` success, `201` created, `400` bad request, `401` unauthenticated, `403` forbidden, `404` not found, `422` validation error, `500` server error

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Properties (Public)](#2-properties-public)
3. [Profile & Account](#3-profile--account)
4. [Bookings](#4-bookings)
5. [Payments](#5-payments)
6. [Notifications](#6-notifications)
7. [Two-Factor Authentication](#7-two-factor-authentication)
8. [Landlord Settings](#8-landlord-settings)
9. [Admin — Dashboard & Analytics](#9-admin--dashboard--analytics)
10. [Admin — User Management](#10-admin--user-management)
11. [Admin — Property Management](#11-admin--property-management)
12. [Admin — Booking Management](#12-admin--booking-management)
13. [Admin — Notification Management](#13-admin--notification-management)
14. [Admin — 2FA Management](#14-admin--2fa-management)
15. [Admin — Settings Management](#15-admin--settings-management)
16. [Admin — Payments & Reports](#16-admin--payments--reports)

---

## 1. Authentication

### POST /register

Register a new user and receive an auth token.

**Body:**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `firstname` | string | Yes | max:255 |
| `lastname` | string | Yes | max:255 |
| `email` | string | Yes | valid email, unique |
| `password` | string | Yes | min:8 |
| `password_confirmation` | string | Yes | must match password |
| `role` | string | Yes | `admin`, `landlord`, `tenant`, `user` |

**Landlord extra fields** (when `role=landlord`):

| Field | Type | Required |
|-------|------|----------|
| `business_name` | string | No |
| `business_registration_number` | string | No |
| `phone` | string | No |
| `address` | string | No |
| `city` | string | No |
| `state` | string | No |
| `postal_code` | string | No |
| `country` | string | No (default: Ghana) |

**Tenant extra fields** (when `role=tenant`):

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `phone` | string | No | |
| `date_of_birth` | date | No | must be before today |
| `gender` | string | No | `male`, `female`, `other` |
| `occupation` | string | No | |
| `employer` | string | No | |
| `monthly_income` | numeric | No | min:0 |
| `current_address` | string | No | |
| `emergency_contact_name` | string | No | |
| `emergency_contact_phone` | string | No | |
| `emergency_contact_relationship` | string | No | |

**Response `201`:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": { "id": 1, "firstname": "John", "role": "landlord", "landlord": {...} },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

---

### POST /login

Authenticate and receive an access token.

**Body:**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `email` | string | Yes | |
| `password` | string | Yes | |
| `two_factor_code` | string | No | 6-digit TOTP code, required if 2FA enabled |
| `recovery_code` | string | No | Use instead of `two_factor_code` |

**Response `200`:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { "id": 1, "firstname": "John", "role": "landlord", "landlord": {...} },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

**Response `422` — 2FA required:**
```json
{
  "success": false,
  "message": "Two-factor authentication required",
  "requires_2fa": true,
  "two_factor_method": "app"
}
```

---

### POST /logout

Revoke the current access token.

**Auth required:** Yes

**Response `200`:**
```json
{ "success": true, "message": "Logged out successfully" }
```

---

## 2. Properties (Public)

These endpoints do not require authentication.

### GET /properties

Browse property listings. Unauthenticated users and non-landlords see only `available` and `is_active=true` properties.

**Query Parameters:**

| Parameter | Type | Default | Notes |
|-----------|------|---------|-------|
| `city` | string | — | Filter by city name |
| `category_id` | integer | — | Filter by property category |
| `min_price` | numeric | — | Minimum monthly rent |
| `max_price` | numeric | — | Maximum monthly rent |
| `bedrooms` | integer | — | Number of bedrooms |
| `furnished` | boolean | — | `true` or `false` |
| `pets_allowed` | boolean | — | `true` or `false` |
| `sort_by` | string | `created_at` | `price`, `created_at`, `views_count`, `bedrooms` |
| `sort_order` | string | `desc` | `asc` or `desc` |
| `per_page` | integer | `15` | Items per page |

**Response `200`:**
```json
{
  "success": true,
  "data": [ { "id": 1, "title": "...", "price": 500, "category": {...}, "landlord": {...} } ],
  "meta": { "total": 100, "per_page": 15, "current_page": 1, "last_page": 7 }
}
```

---

### GET /properties/{id}

Get a single property. View count is incremented for non-owners.

**Response `200`:**
```json
{
  "success": true,
  "data": { "id": 1, "title": "...", "views_count": 42, "category": {...}, "landlord": {...} }
}
```

---

### GET /property-categories

Get all active property categories.

**Response `200`:**
```json
{
  "success": true,
  "data": [ { "id": 1, "name": "Apartment", "slug": "apartment" } ]
}
```

---

## 3. Profile & Account

**Auth required:** Yes

### GET /user

Returns the raw authenticated user record.

---

### GET /profile

Get full profile including role-specific details and preferences. Creates default preferences if none exist.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "firstname": "John",
    "role": "landlord",
    "landlord": { "business_name": "...", "verified": false },
    "preferences": {
      "timezone": "Africa/Lagos",
      "date_format": "DD/MM/YYYY",
      "theme": "light",
      "dashboard_refresh_interval": 30,
      "email_notifications": true,
      "sms_notifications": false,
      "bio": null
    }
  }
}
```

---

### PUT /profile

Update user profile. Send only the fields you want to change.

**Body (all optional):**

| Field | Type | Validation |
|-------|------|------------|
| `firstname` | string | max:255 |
| `lastname` | string | max:255 |
| `email` | string | unique |
| `bio` | string | max:1000 |
| `preferences.timezone` | string | max:50 |
| `preferences.date_format` | string | `DD/MM/YYYY`, `MM/DD/YYYY`, `YYYY-MM-DD` |
| `preferences.theme` | string | `light`, `dark` |
| `preferences.dashboard_refresh_interval` | integer | 10–300 |
| `preferences.notifications.email` | boolean | |
| `preferences.notifications.sms` | boolean | |
| `preferences.notifications.new_bookings` | boolean | |
| `preferences.notifications.property_updates` | boolean | |
| `preferences.notifications.user_registrations` | boolean | |
| `preferences.notifications.system_alerts` | boolean | |
| `preferences.notifications.weekly_reports` | boolean | |

**Landlord profile fields** (if role=landlord):
`business_name`, `business_registration_number`, `phone`, `address`, `city`, `state`, `country`, `postal_code`

**Tenant profile fields** (if role=tenant):
`phone`, `date_of_birth`, `gender`, `occupation`, `employer`, `monthly_income`, `current_address`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relationship`

---

### PUT /change-password

Change the authenticated user's password.

**Body:**

| Field | Type | Required |
|-------|------|----------|
| `current_password` | string | Yes |
| `password` | string | Yes (min:8) |
| `password_confirmation` | string | Yes |

**Response `200`:**
```json
{ "success": true, "message": "Password changed successfully" }
```

---

## 4. Bookings

**Auth required:** Yes

### GET /browse-available-properties

Browse properties available for booking. Excludes properties with active `pending` or `confirmed` bookings.

**Query Parameters:** Same as `GET /properties` plus:

| Parameter | Type | Notes |
|-----------|------|-------|
| `available_from` | date | Show properties available from this date |

---

### POST /bookings

Submit a booking request and initialize a Paystack payment. The booking is created immediately with `payment_status=unpaid`. The landlord is only notified **after** payment succeeds.

**Body:**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `property_id` | integer | Yes | must exist |
| `move_in_date` | date | Yes | after today |
| `lease_duration_months` | integer | Yes | 1–60 |
| `tenant_name` | string | Yes | max:255 |
| `tenant_phone` | string | Yes | max:20 |
| `tenant_email` | string | Yes | valid email |
| `tenant_message` | string | No | max:1000 |
| `occupation` | string | No | |
| `employer` | string | No | |
| `monthly_income` | numeric | No | min:0 |
| `emergency_contact` | string | No | max:500 |

**Notes:**
- Property must have `availability_status = available`
- Property must not have an existing `pending` or `confirmed` booking
- `move_out_date` is calculated automatically: `move_in_date + lease_duration_months`
- `total_amount` = `monthly_rent + security_deposit`
- A Paystack transaction is initialized — redirect the tenant to `payment_url` to complete payment
- Use `POST /payments/verify` with the returned `reference` after Paystack redirects back

**Response `201`:**
```json
{
  "success": true,
  "message": "Booking created. Please complete payment to confirm your request.",
  "data": {
    "booking": {
      "id": 5,
      "status": "pending",
      "payment_status": "unpaid",
      "move_in_date": "2026-07-01",
      "move_out_date": "2026-07-01",
      "monthly_rent": "500.00",
      "security_deposit": "500.00",
      "total_amount": "1000.00",
      "currency": "GHS",
      "property": {...},
      "landlord": {...}
    },
    "payment": {
      "reference": "EFIEW-A1B2C3D4-1716000000",
      "amount": 1000.00,
      "currency": "GHS",
      "payment_url": "https://checkout.paystack.com/...",
      "access_code": "acc_..."
    }
  }
}
```

---

### POST /test-bookings

Create a booking with past or future dates — for notification testing only. Does **not** initialize a Paystack payment.

**Availability:** Non-production environments only (`APP_ENV != production`)

**Body:** Same as `POST /bookings` except `move_in_date` has no `after:today` constraint.

---

### GET /my-bookings

Get the authenticated user's bookings.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | `pending`, `confirmed`, `cancelled`, `completed` |
| `per_page` | integer | Default: 15 |

**Response `200`:**
```json
{
  "success": true,
  "data": [...],
  "meta": { "total": 5, "per_page": 15, "current_page": 1, "last_page": 1 },
  "summary": { "total_bookings": 5, "pending_bookings": 2, "confirmed_bookings": 1 }
}
```

---

### GET /bookings/{id}

Get a specific booking. User must own the booking.

---

### PATCH /bookings/{id}/cancel

Cancel a booking. Only `pending` bookings can be cancelled by the tenant.

**Response `200`:**
```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": { "booking_id": 5, "new_status": "cancelled" }
}
```

---

### POST /bookings/{id}/pay-rent

Initialize a monthly rent renewal payment for a confirmed booking. Can only be called on bookings with `status=confirmed` and `payment_status=paid` (i.e. the initial booking payment was already completed). Returns a new Paystack `payment_url` for the tenant to pay this month's rent.

**Auth required:** Yes (must own the booking)

**Notes:**
- Returns `400` if a pending rent payment already exists for the booking
- After Paystack redirects back, call `POST /payments/verify` with the returned `reference`

**Response `200`:**
```json
{
  "success": true,
  "message": "Rent payment initialized. Please complete payment.",
  "data": {
    "payment": { "id": 12, "type": "rent_renewal", "amount": "500.00", "status": "pending" },
    "reference": "EFIEW-E5F6G7H8-1716000001",
    "amount": 500.00,
    "currency": "GHS",
    "payment_url": "https://checkout.paystack.com/...",
    "access_code": "acc_..."
  }
}
```

---

### GET /bookings

**(Landlord only)** Get all booking requests for the landlord's properties.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | Filter by status |
| `property_id` | integer | Filter by specific property |
| `per_page` | integer | Default: 15 |

---

### PATCH /bookings/{id}/confirm

**(Landlord only)** Confirm a pending booking. Sets property `availability_status` to `occupied` and notifies the tenant.

**Response `200`:**
```json
{
  "success": true,
  "message": "Booking confirmed successfully",
  "data": { "booking_id": 5, "new_status": "confirmed" }
}
```

---

### PATCH /bookings/{id}/reject

**(Landlord only)** Reject a pending booking. Notifies the tenant.

---

## 5. Payments

Payments are processed via Paystack. The general flow is:

1. `POST /bookings` → returns `payment_url` — redirect the tenant there
2. Tenant completes payment on Paystack
3. Paystack either calls the webhook (`POST /payments/webhook`) **or** the tenant returns to your app and you call `POST /payments/verify`
4. On success: `booking.payment_status` becomes `paid`, landlord is notified
5. On failure: booking is auto-cancelled

**Auth required:** Yes (except the webhook endpoint)

---

### POST /payments/verify

Verify a Paystack payment using the reference returned at booking creation or rent renewal. Call this after Paystack redirects the tenant back to your application.

**Body:**

| Field | Type | Required |
|-------|------|----------|
| `reference` | string | Yes |

**Notes:**
- The reference must belong to the authenticated user
- Idempotent — returns success immediately if already verified
- On success: updates `payment.status=paid`, `booking.payment_status=paid`, notifies landlord
- On failure: updates `payment.status=failed`, booking auto-cancelled (for booking payments)

**Response `200` (success):**
```json
{
  "success": true,
  "message": "Payment verified successfully",
  "data": {
    "id": 3,
    "type": "booking_payment",
    "status": "paid",
    "amount": "1000.00",
    "currency": "GHS",
    "payment_channel": "card",
    "paid_at": "2026-05-17T10:30:00.000000Z",
    "paystack_reference": "EFIEW-A1B2C3D4-1716000000",
    "booking": {
      "id": 5,
      "status": "pending",
      "payment_status": "paid",
      "property": {...}
    }
  }
}
```

**Response `200` (failed payment):**
```json
{
  "success": false,
  "message": "Payment was not successful",
  "data": { "id": 3, "status": "failed", "failure_reason": "Insufficient funds" }
}
```

---

### POST /payments/webhook

Paystack webhook endpoint. Called by Paystack servers to deliver payment events in real time. This endpoint is **unauthenticated** but validates the `X-Paystack-Signature` HMAC header.

> **Do not call this endpoint directly from your app.** Configure it as the webhook URL in your Paystack dashboard.

**Headers required:**
```
X-Paystack-Signature: <sha512 HMAC of raw body signed with PAYSTACK_SECRET_KEY>
```

**Handled events:**

| Event | Action |
|-------|--------|
| `charge.success` | Marks payment as paid, updates booking `payment_status=paid`, notifies landlord and tenant |
| `charge.failed` | Marks payment as failed, auto-cancels booking (for booking payments), notifies tenant |
| `refund.processed` | Marks payment as refunded, updates booking `payment_status=refunded` |

**Response `200`:**
```json
{ "message": "Webhook received" }
```

**Response `401` (invalid signature):**
```json
{ "message": "Invalid signature" }
```

---

### GET /payments/{id}

Get details of a single payment. The authenticated user must own the payment or be an admin.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "type": "booking_payment",
    "amount": "1000.00",
    "currency": "GHS",
    "status": "paid",
    "paystack_reference": "EFIEW-A1B2C3D4-1716000000",
    "paystack_transaction_id": "1234567890",
    "payment_channel": "card",
    "paid_at": "2026-05-17T10:30:00.000000Z",
    "booking": { "id": 5, "status": "pending", "payment_status": "paid", "property": {...} },
    "landlord": { "id": 2, "user": {...} }
  }
}
```

---

### GET /my-payments

Get the authenticated tenant's full payment history.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | `pending`, `paid`, `failed`, `refunded` |
| `type` | string | `booking_payment`, `rent_renewal`, `refund` |
| `per_page` | integer | Default: 15 |

**Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "type": "booking_payment",
      "amount": "1000.00",
      "status": "paid",
      "paid_at": "2026-05-17T10:30:00.000000Z",
      "booking": { "id": 5, "property": {...} }
    }
  ],
  "meta": { "total": 5, "per_page": 15, "current_page": 1, "last_page": 1 },
  "summary": {
    "paid_transactions": 4,
    "total_paid": 3500.00
  }
}
```

---

### GET /landlord/payments

**(Landlord only)** Get all payments received for the landlord's properties, with a 12-month monthly breakdown.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | `pending`, `paid`, `failed`, `refunded` |
| `type` | string | `booking_payment`, `rent_renewal` |
| `per_page` | integer | Default: 15 |

**Response `200`:**
```json
{
  "success": true,
  "data": [...],
  "meta": { "total": 20, "per_page": 15, "current_page": 1, "last_page": 2 },
  "summary": {
    "total_received": 15000.00,
    "transaction_count": 20,
    "monthly_breakdown": [
      { "month": "2026-04", "revenue": 3000.00, "count": 4 },
      { "month": "2026-05", "revenue": 2500.00, "count": 3 }
    ]
  }
}
```

---

## 6. Notifications

**Auth required:** Yes

### GET /notifications

Get the authenticated user's notifications.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `unread_only` | boolean | `true` returns only unread |
| `type` | string | Filter by notification type |
| `priority` | string | `urgent`, `high`, `medium`, `low` |
| `per_page` | integer | Default: 20 |

**Response `200`:**
```json
{
  "success": true,
  "data": [...],
  "meta": { "total": 10, "per_page": 20, "current_page": 1, "last_page": 1 },
  "summary": { "total_notifications": 10, "unread_count": 3, "high_priority_unread": 1 }
}
```

---

### GET /notifications/counts

Get a count breakdown of notifications by read status, priority, and type.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "total": 10,
    "unread": 3,
    "read": 7,
    "by_priority": { "urgent": 0, "high": 1, "medium": 2, "low": 0 },
    "by_type": {
      "booking_confirmed": 1,
      "payment_received": 2,
      "payment_failed": 0
    }
  }
}
```

---

### GET /notifications/types

Get all available notification types with descriptions.

**Notification types:**

| Type | Category | Priority | Sent to |
|------|----------|----------|---------|
| `booking_confirmed` | booking | high | tenant |
| `booking_rejected` | booking | medium | tenant |
| `new_booking_request` | booking | high | landlord |
| `property_viewed_milestone` | property | low | landlord |
| `welcome_message` | system | medium | new user |
| `lease_expiry_reminder` | lease | medium–urgent | tenant & landlord |
| `payment_reminder_5_days` | payment | medium | tenant |
| `payment_due_today` | payment | high | tenant |
| `payment_overdue_3_days` | payment | urgent | tenant |
| `move_in_reminder_7_days` | move | medium | tenant |
| `move_in_today` | move | high | tenant |
| `move_out_reminder` | move | high | tenant |
| `property_auto_released` | system | urgent | tenant & landlord |
| `payment_received` | payment | high | tenant |
| `payment_failed` | payment | urgent | tenant |
| `refund_processed` | payment | high | tenant |

---

### GET /notifications/email-status

Get email delivery status and stats for the user's notifications.

**Query:** `per_page` (default: 20)

**Response `200`:**
```json
{
  "success": true,
  "data": [ { "id": 1, "type": "...", "title": "...", "email_sent": true } ],
  "meta": { "total": 10, "per_page": 20, "current_page": 1, "last_page": 1 },
  "stats": { "total_notifications": 10, "emails_sent": 8, "emails_pending": 2, "delivery_rate": 80.0 }
}
```

---

### PATCH /notifications/{id}/read

Mark a notification as read. User must own the notification.

**Response `200`:**
```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": { "notification_id": 1, "is_read": true, "read_at": "2026-05-15T09:00:00Z" }
}
```

---

### PATCH /notifications/{id}/unread

Mark a notification as unread. User must own the notification.

---

### PATCH /notifications/mark-all-read

Mark all unread notifications as read.

**Response `200`:**
```json
{
  "success": true,
  "message": "Marked 3 notifications as read",
  "data": { "updated_count": 3 }
}
```

---

### POST /notifications/{id}/resend-email

Resend the email for a notification. User must own the notification.

**Response `200`:**
```json
{
  "success": true,
  "message": "Email notification resent successfully",
  "data": { "notification_id": 1, "email_sent": true, "sent_at": "2026-05-15T09:00:00Z" }
}
```

---

### DELETE /notifications/{id}

Delete a notification. User must own the notification.

---

### DELETE /notifications/clear-read

Delete all read notifications for the authenticated user.

**Response `200`:**
```json
{
  "success": true,
  "message": "Deleted 7 read notifications",
  "data": { "deleted_count": 7 }
}
```

---

## 7. Two-Factor Authentication

**Auth required:** Yes

### GET /2fa/status

Get the current user's 2FA configuration.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "two_factor_enabled": false,
    "two_factor_confirmed": false,
    "two_factor_required": false,
    "two_factor_method": "app",
    "recovery_codes_generated": false,
    "recovery_codes_remaining": 0
  }
}
```

---

### POST /2fa/generate-secret

Generate a new 2FA secret and QR code. The secret is stored but not activated until confirmed.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "secret": "JBSWY3DPEHPK3PXP",
    "qr_code": "data:image/png;base64,...",
    "qr_code_url": "otpauth://totp/Efiewura:user@example.com?secret=...",
    "manual_entry_key": "JBSWY3DPEHPK3PXP",
    "backup_codes": null
  }
}
```

> **Note:** If the GD PHP extension is not available, `qr_code` will be an external URL to a QR code image service.

---

### POST /2fa/confirm

Confirm 2FA setup by verifying a code from the authenticator app. Returns one-time recovery codes.

**Body:**

| Field | Type | Required |
|-------|------|----------|
| `code` | string | Yes (exactly 6 chars) |

**Response `200`:**
```json
{
  "success": true,
  "message": "2FA has been successfully enabled",
  "data": {
    "recovery_codes": ["abc-def-123", "ghi-jkl-456"],
    "enabled": true
  }
}
```

---

### POST /2fa/verify

Verify a 2FA code (TOTP or recovery code). Used after login when 2FA is required.

**Body:**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `code` | string | Yes | TOTP code or recovery code |
| `recovery` | boolean | No | Set to `true` if `code` is a recovery code |

---

### POST /2fa/disable

Disable 2FA for the authenticated user. Requires password confirmation.

**Body:**

| Field | Type | Required |
|-------|------|----------|
| `password` | string | Yes |

**Response `200`:**
```json
{ "success": true, "message": "2FA has been disabled" }
```

---

### GET /2fa/recovery-codes

Retrieve current recovery codes. Requires 2FA to be enabled.

**Response `200`:**
```json
{
  "success": true,
  "data": { "recovery_codes": ["abc-def-123"], "codes_remaining": 8 }
}
```

---

### POST /2fa/recovery-codes/regenerate

Generate a new set of recovery codes, invalidating old ones. Requires password confirmation.

**Body:**

| Field | Type | Required |
|-------|------|----------|
| `password` | string | Yes |

**Response `200`:**
```json
{
  "success": true,
  "message": "New recovery codes generated",
  "data": { "recovery_codes": ["abc-def-123"] }
}
```

---

## 8. Landlord Settings

**Auth required:** Yes, role: `landlord`

### GET /landlord/settings

Get the landlord's own settings including auto-release configuration.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "business_name": "Kofi Properties",
    "phone": "+233501234567",
    "address": "123 High Street",
    "city": "Accra",
    "state": "Greater Accra",
    "country": "Ghana",
    "commission_rate": 10.0,
    "overdue_release_days": 30,
    "status": "active",
    "verified": false
  }
}
```

---

### PATCH /landlord/settings

Update landlord settings. Send only the fields you want to change.

**Body (all optional):**

| Field | Type | Validation |
|-------|------|------------|
| `business_name` | string | max:255 |
| `phone` | string | max:20 |
| `address` | string | max:500 |
| `city` | string | max:100 |
| `state` | string | max:100 |
| `country` | string | max:100 |
| `commission_rate` | numeric | 0–100 |
| `overdue_release_days` | integer | 7–365 |

> `overdue_release_days` controls how many days after a booking becomes overdue before the property is automatically released back to available status.

---

## 9. Admin — Dashboard & Analytics

**Auth required:** Yes, role: `admin`  
**Base path:** `/api/admin`

### GET /admin/dashboard

Get a platform-wide overview.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "users": { "total": 120, "active": 95, "new_this_month": 8, "landlords": 30, "tenants": 80, "admins": 2 },
    "properties": { "total": 45, "active": 30, "pending_review": 10, "suspended": 5, "new_this_month": 3 },
    "bookings": { "total": 200, "pending": 15, "confirmed": 60, "completed": 110, "cancelled": 15, "revenue_this_month": 12500.00 },
    "notifications": { "total": 500, "unread": 45, "critical": 2 },
    "system": { "uptime": "99.8%", "last_backup": "...", "storage_used": "78%", "active_sessions": 95 }
  }
}
```

---

### GET /admin/analytics

Get summary analytics for a given period.

**Query Parameters:**

| Parameter | Type | Default | Notes |
|-----------|------|---------|-------|
| `period` | string | `30d` | `7d`, `30d`, `90d`, `1y` |
| `end_date` | date | today | Custom end date |

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "users": { "total_signups": 25, "active_users": 95, "retention_rate": 79.2, "churn_rate": 20.8 },
    "properties": { "new_listings": 10, "total_views": 1200, "booking_conversion": 4.2 },
    "bookings": { "total_bookings": 50, "total_revenue": 25000.00, "average_booking_value": 500.00, "cancellation_rate": 8.0 },
    "growth": { "user_growth": 12.5, "property_growth": 5.0, "revenue_growth": 18.3 }
  }
}
```

---

### GET /admin/analytics/user-growth

Get time-series user growth chart data.

**Query Parameters:**

| Parameter | Type | Default |
|-----------|------|---------|
| `period` | string | `30d` |
| `granularity` | string | `daily` (`daily`, `weekly`, `monthly`) |

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "labels": ["2026-04-15", "2026-04-16"],
    "datasets": {
      "total_users": [110, 112],
      "new_signups": [2, 2],
      "landlords": [28, 29],
      "tenants": [72, 73]
    }
  }
}
```

---

### GET /admin/analytics/booking-trends

Get time-series booking trend data.

**Query Parameters:** `period`, `granularity` (same as user-growth)

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "labels": ["2026-04-15"],
    "datasets": {
      "total_bookings": [5],
      "confirmed_bookings": [3],
      "revenue": [1500.00]
    }
  }
}
```

---

### GET /admin/analytics/revenue

Get revenue analytics for the last 12 months.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "total_revenue": 120000.00,
    "commission_earned": 12000.00,
    "monthly_recurring": 80000.00,
    "one_time_payments": 40000.00,
    "trends": {
      "labels": ["Jun", "Jul", "Aug"],
      "revenue": [9000.00, 10500.00],
      "commissions": [900.00, 1050.00]
    }
  }
}
```

---

### GET /admin/settings

Get legacy system settings (static snapshot). Use `/admin/settings/` (with trailing slash) for the full configurable settings system.

---

### PATCH /admin/settings

Update legacy system settings. **Not fully implemented** — returns the request body as-is.

---

### GET /admin/logs

Retrieve application log entries.

**Query Parameters:**

| Parameter | Type | Default |
|-----------|------|---------|
| `type` | string | `application` |
| `lines` | integer | `100` |

---

### POST /admin/backup

Trigger a system backup. **Not fully implemented** — returns a placeholder response.

---

## 10. Admin — User Management

### GET /admin/users

List all users with filters and pagination.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `role` | string | `admin`, `landlord`, `tenant`, `user` |
| `search` | string | Searches name and email |
| `verified` | boolean | Filter by verification status |
| `date_from` | date | Created on or after |
| `date_to` | date | Created on or before |
| `sort_by` | string | Default: `created_at` |
| `sort_order` | string | `asc` or `desc` |
| `per_page` | integer | Default: 15 |

**Response `200`:** Paginated user list with landlord/tenant profiles loaded.

---

### GET /admin/users/{id}

Get a specific user with their bookings, notifications, and profile.

---

### GET /admin/users/{id}/profile

Get the landlord or tenant profile for a user, including activity stats.

**Landlord response includes:** `properties_count`, `active_properties_count`, `bookings_count`, `total_revenue`

**Tenant response includes:** `bookings_count`, `active_bookings_count`, `completed_bookings_count`

---

### PATCH /admin/users/{id}

Update a user's core data or profile status.

**Body (all optional):**

| Field | Type | Validation |
|-------|------|------------|
| `name` | string | max:255 |
| `email` | string | unique |
| `role` | string | `admin`, `landlord`, `tenant`, `user` |
| `verified` | boolean | Updates the landlord/tenant `verified` flag and `verified_at` |
| `status` | string | `active`, `inactive`, `suspended` |

---

### POST /admin/users/{id}/actions

Perform a moderation action on a user account.

**Body:**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `action` | string | Yes | `suspend`, `activate`, `ban`, `verify_email`, `reset_password` |
| `reason` | string | No | max:500 |
| `duration` | integer | No | Days (1–365), only used with `suspend` |

**Action effects:**
- `suspend` — Sets status to `suspended`, deactivates landlord properties. Duration sets `suspension_until`.
- `activate` — Restores status to `active` on user and their profile.
- `ban` — Sets status to `banned`, permanently deactivates all properties, cancels pending tenant bookings.
- `verify_email` — Sets `email_verified_at` to now.
- `reset_password` — Generates a temporary password. Returns temp password in the response message.

---

### DELETE /admin/users/{id}

Delete a user. Soft-deletes related records. Fails if the user has active bookings.

**Response `400` if active bookings exist:**
```json
{
  "success": false,
  "message": "Cannot delete user with active bookings...",
  "active_bookings": 2
}
```

---

## 11. Admin — Property Management

### GET /admin/properties

List all properties with filters.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | `available`, `occupied`, `under_maintenance`, `unavailable` |
| `city` | string | Partial match |
| `category_id` | integer | |
| `landlord_id` | integer | |
| `search` | string | Searches title, description, address |
| `price_min` | numeric | Minimum monthly rent |
| `price_max` | numeric | Maximum monthly rent |
| `active` | boolean | Filter by `is_active` |
| `sort_by`, `sort_order`, `per_page` | — | Standard pagination |

---

### PATCH /admin/properties/{id}

Update a property's admin-controlled fields.

**Body (all optional):**

| Field | Type | Validation |
|-------|------|------------|
| `availability_status` | string | `available`, `occupied`, `under_maintenance`, `unavailable` |
| `is_active` | boolean | |
| `admin_notes` | string | max:1000 |

> Updating `availability_status` or `is_active` sends a notification to the property's landlord.

---

### PUT /admin/properties/{id}/status

Alias for `PATCH /admin/properties/{id}` — accepts the same body.

---

### POST /admin/properties/{id}/moderate

Perform a moderation action on a property.

**Body:**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `action` | string | Yes | `approve`, `reject`, `flag`, `unflag`, `feature` |
| `reason` | string | No | max:500 |
| `feedback` | string | No | max:1000, sent to landlord |

**Action effects:**

| Action | Effect |
|--------|--------|
| `approve` | `is_active=true`, `availability_status=available`, sets `admin_approved_at` |
| `reject` | `is_active=false`, `availability_status=under_maintenance`, sets `admin_rejected_at`, stores `rejection_reason` |
| `flag` | Sets `flagged_at`, `flagged_reason` |
| `unflag` | Clears `flagged_at`, `flagged_reason` |
| `feature` | Sets `featured=true`, `featured_at` |

> A notification is sent to the landlord for all actions.

---

## 12. Admin — Booking Management

### GET /admin/bookings

List all bookings with filters.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | `pending`, `confirmed`, `cancelled`, `rejected`, `completed` |
| `user_id` | integer | Filter by tenant |
| `landlord_id` | integer | Filter by landlord |
| `property_id` | integer | |
| `date_from` | date | Created on or after |
| `date_to` | date | Created on or before |
| `amount_min` | numeric | Minimum total amount |
| `amount_max` | numeric | Maximum total amount |
| `sort_by`, `sort_order`, `per_page` | — | Standard pagination |

---

### PATCH /admin/bookings/{id}

Update a booking's status or add admin notes.

**Body (all optional):**

| Field | Type | Validation |
|-------|------|------------|
| `status` | string | `pending`, `confirmed`, `cancelled`, `rejected`, `completed` |
| `admin_notes` | string | max:1000 |
| `cancellation_reason` | string | max:500 |

**Side effects on status change:**
- `confirmed` — Sets property `availability_status=occupied`
- `cancelled` or `rejected` (from confirmed) — Sets property `availability_status=available`
- Status changes notify both the tenant and the landlord

---

## 13. Admin — Notification Management

### GET /admin/notifications

List all notifications across the platform.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | `active` (default), `deleted`, `all` |
| `type` | string | Filter by notification type |
| `priority` | string | `low`, `medium`, `high`, `urgent` |
| `read_status` | string | `read` or `unread` |
| `email_status` | boolean | Filter by email delivery status |
| `user_id` | integer | Filter by recipient |
| `date_from`, `date_to` | date | Date range filter |
| `sort_by`, `sort_order`, `per_page` | — | Standard pagination |

---

### POST /admin/notifications

Create and broadcast a notification to one or more users.

**Body:**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `type` | string | Yes | max:50 |
| `title` | string | Yes | max:255 |
| `message` | string | Yes | max:1000 |
| `priority` | string | Yes | `low`, `medium`, `high` |
| `target_users` | array | Yes | Array of user IDs (must exist) |
| `send_email` | boolean | No | Default: `false` |
| `data` | object | No | Extra metadata |

**Response `200`:**
```json
{
  "success": true,
  "message": "Notifications created successfully",
  "data": { "total_sent": 3, "email_sent": false, "notifications": [...] }
}
```

---

### PATCH /admin/notifications/{id}/read

Mark a specific notification as read (sets `read_at` and `read_by` admin ID).

---

### PATCH /admin/notifications/read-multiple

Mark multiple notifications as read.

**Body:**

| Field | Type | Required |
|-------|------|----------|
| `notification_ids` | array of integers | Yes (min 1 item) |

**Response `200`:**
```json
{
  "success": true,
  "message": "Successfully marked 5 notifications as read",
  "data": { "updated_count": 5, "notification_ids": [1, 2, 3, 4, 5] }
}
```

---

### DELETE /admin/notifications/{id}

Soft-delete a notification (sets `status=deleted`, `deleted_at`, `deleted_by`).

---

### PATCH /admin/notifications/{id}/restore

Restore a soft-deleted notification.

---

## 14. Admin — 2FA Management

### GET /admin/2fa/overview

Get a list of all users with their 2FA status.

**Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "landlord",
      "two_factor_enabled": true,
      "two_factor_required": false,
      "two_factor_setup": true,
      "setup_date": "2026-03-01 10:00:00"
    }
  ]
}
```

---

### GET /admin/2fa/users/{id}/status

Get 2FA configuration for a specific user.

---

### PATCH /admin/2fa/users/{id}/toggle-requirement

Force-require or un-require 2FA for a specific user.

**Body:**

| Field | Type | Required |
|-------|------|----------|
| `required` | boolean | Yes |

**Response `200`:**
```json
{
  "success": true,
  "message": "2FA requirement updated for user",
  "data": { "user_id": 5, "email": "user@example.com", "two_factor_required": true }
}
```

---

### DELETE /admin/2fa/users/{id}/disable

Force-disable 2FA for a user without requiring their password.

**Response `200`:**
```json
{
  "success": true,
  "message": "2FA has been disabled for user by admin",
  "data": { "user_id": 5, "email": "user@example.com" }
}
```

---

## 15. Admin — Settings Management

A persistent settings system organized into six categories.

**Base path:** `/api/admin/settings/`

### Categories and their default values

| Category | Key | Default | Type |
|----------|-----|---------|------|
| **general** | `platform_name` | `Efiewura Property Management` | string |
| | `platform_description` | `Modern property rental management platform for Ghana` | string |
| | `support_email` | `support@efiewura.com` | string |
| | `support_phone` | `+233 123 456 789` | string |
| | `timezone` | `Africa/Accra` | string |
| **users** | `allow_registration` | `true` | boolean |
| | `require_email_verification` | `true` | boolean |
| | `require_manual_approval` | `false` | boolean |
| | `password_min_length` | `8` | integer |
| | `session_timeout` | `120` | integer (minutes) |
| | `require_special_chars` | `true` | boolean |
| **properties** | `auto_approve` | `false` | boolean |
| | `max_images` | `20` | integer |
| | `max_file_size` | `10` | integer (MB) |
| | `commission_rate` | `10.0` | float (%) |
| **payments** | `default_currency` | `GHS` | string |
| | `payment_timeout` | `30` | integer (minutes) |
| | `paypal`, `stripe`, `paystack`, `mobile_money` | `{"enabled": true}` | json |
| **notifications** | `email` | `{"new_user": true, "new_property": true, "booking_confirm": true}` | json |
| | `system` | `{"low_disk_space": true, "failed_payments": true}` | json |
| **security** | `require_2fa` | `false` | boolean |
| | `max_login_attempts` | `5` | integer |
| | `lockout_duration` | `30` | integer (minutes) |
| | `data_retention_days` | `365` | integer |

---

### GET /admin/settings/

Get all settings grouped by category. Initializes defaults on first call.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "general": { "platform_name": "Efiewura..." },
    "users": { "allow_registration": true },
    "properties": { "commission_rate": 10.0 },
    "payments": { "default_currency": "GHS" },
    "notifications": {},
    "security": { "require_2fa": false }
  }
}
```

---

### PUT /admin/settings/

Update any number of settings across any categories in a single request.

**Body:** Nested object — category keys contain setting key-value pairs.

```json
{
  "general": { "platform_name": "My Platform", "support_email": "help@myapp.com" },
  "security": { "require_2fa": true, "max_login_attempts": 3 }
}
```

---

### POST /admin/settings/reset

Reset all settings to factory defaults. Clears the settings table and reinserts defaults.

---

### GET /admin/settings/{category}

Get settings for one category. Available categories: `general`, `users`, `properties`, `payments`, `notifications`, `security`.

---

### PUT /admin/settings/{category}

Update settings for one category only.

**Body:** Flat key-value object for that category.

```json
{ "commission_rate": 12.5, "auto_approve": true }
```

---

## 16. Admin — Payments & Reports

**Auth required:** Yes, role: `admin`  
**Base path:** `/api/admin`

---

### POST /admin/payments/{id}/refund

Initiate a refund for a paid transaction via Paystack. The payment must have `status=paid` and must not have been refunded already. Notifies the tenant by email and in-app notification.

**Body:**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `refund_reason` | string | Yes | max:1000 |
| `refund_amount` | numeric | No | min:0.01, max: original amount. Omit for a full refund. |

**Response `200`:**
```json
{
  "success": true,
  "message": "Refund processed successfully",
  "data": {
    "id": 3,
    "status": "refunded",
    "amount": "1000.00",
    "refund_amount": "1000.00",
    "refund_reference": "ref_...",
    "refunded_at": "2026-05-17T11:00:00.000000Z",
    "refund_reason": "Landlord rejected booking",
    "booking": { "id": 5, "payment_status": "refunded", "property": {...} }
  }
}
```

**Response `400` — not paid:**
```json
{ "success": false, "message": "Only paid transactions can be refunded" }
```

**Response `422` — amount exceeds original:**
```json
{ "success": false, "message": "Refund amount cannot exceed the original payment amount" }
```

---

### GET /admin/reports/transactions

Paginated list of all payment transactions with summary totals.

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `status` | string | `pending`, `paid`, `failed`, `refunded` |
| `type` | string | `booking_payment`, `rent_renewal`, `refund` |
| `date_from` | date | `created_at` on or after |
| `date_to` | date | `created_at` on or before |
| `landlord_id` | integer | Filter by landlord |
| `user_id` | integer | Filter by tenant |
| `booking_id` | integer | Filter by specific booking |
| `per_page` | integer | Default: 20 |

**Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "type": "booking_payment",
      "amount": "1000.00",
      "currency": "GHS",
      "status": "paid",
      "paystack_reference": "EFIEW-A1B2C3D4-1716000000",
      "payment_channel": "card",
      "paid_at": "2026-05-17T10:30:00.000000Z",
      "booking": { "id": 5, "property": { "title": "..." } },
      "user": { "id": 10, "name": "Ama Asante", "email": "ama@example.com" },
      "landlord": { "id": 2, "user": { "name": "Kofi Mensah" } }
    }
  ],
  "meta": { "total": 80, "per_page": 20, "current_page": 1, "last_page": 4 },
  "summary": {
    "paid": 60,
    "pending": 5,
    "failed": 10,
    "refunded": 5,
    "total_collected": 45000.00,
    "total_refunded": 2000.00,
    "net_revenue": 43000.00
  }
}
```

---

### GET /admin/reports/revenue

Overall revenue summary with a 12-month trend and breakdown by payment type.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "total_collected": 45000.00,
    "total_refunded": 2000.00,
    "net_revenue": 43000.00,
    "transaction_count": 60,
    "by_type": {
      "booking_payments": 30000.00,
      "rent_renewals": 15000.00
    },
    "monthly_trend": [
      { "month": "2025-06", "revenue": 2500.00, "count": 4 },
      { "month": "2025-07", "revenue": 3200.00, "count": 5 },
      { "month": "2026-05", "revenue": 4800.00, "count": 7 }
    ]
  }
}
```

> `monthly_trend` always covers the last 12 calendar months, with `revenue: 0` for months with no payments.

---

### GET /admin/reports/landlord/{id}/revenue

Revenue breakdown for a specific landlord.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "landlord": { "id": 2, "business_name": "Kofi Properties", "user": {...} },
    "total_received": 12000.00,
    "total_refunded": 500.00,
    "net_revenue": 11500.00,
    "transaction_count": 15,
    "active_bookings": 4,
    "monthly_trend": [
      { "month": "2026-03", "revenue": 2000.00, "count": 3 },
      { "month": "2026-04", "revenue": 3500.00, "count": 5 }
    ]
  }
}
```

---

### GET /admin/reports/tenant/{id}/payments

Full payment history for a specific tenant (admin view).

**Query Parameters:**

| Parameter | Type | Notes |
|-----------|------|-------|
| `per_page` | integer | Default: 20 |

**Response `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "type": "booking_payment",
      "amount": "1000.00",
      "status": "paid",
      "paid_at": "2026-05-17T10:30:00.000000Z",
      "booking": { "id": 5, "property": { "title": "..." } },
      "landlord": { "id": 2, "user": { "name": "Kofi Mensah" } }
    }
  ],
  "meta": { "total": 5, "per_page": 20, "current_page": 1, "last_page": 1 },
  "summary": {
    "user": { "id": 10, "name": "Ama Asante", "email": "ama@example.com" },
    "paid_transactions": 4,
    "total_paid": 3500.00,
    "refunded_transactions": 1,
    "total_refunded": 500.00
  }
}
```

---

## Pagination

All paginated endpoints accept `per_page` (integer, default 15 or 20) and return a `meta` object:

```json
{
  "meta": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7
  }
}
```

---

## Error Responses

### Validation Error `422`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": { "field_name": ["Error message"] }
}
```

### Unauthorized `401`
```json
{ "success": false, "message": "Unauthenticated." }
```

### Forbidden `403`
```json
{ "success": false, "message": "Unauthorized to perform this action" }
```

### Not Found `404`
```json
{ "success": false, "message": "Resource not found" }
```

### Server Error `500`
```json
{ "success": false, "message": "...", "error": "Exception message" }
```
