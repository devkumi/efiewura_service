# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Efiewura** is a Laravel 12 property rental management platform that connects landlords and tenants with:
- Property listing and booking workflow
- Automated time-based notifications (lease expiry, payment reminders, move-in/out)
- Role-based access control (admin, landlord, tenant, user)
- Comprehensive admin portal with analytics
- Two-factor authentication support

**Tech Stack**: Laravel 12, PHP 8.2+, SQLite/MySQL, Laravel Sanctum, Vite + Tailwind CSS

## Development Commands

### Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Development Server
```bash
# Start all services concurrently
composer run dev

# Or individually:
php artisan serve                           # http://localhost:8000
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Testing
```bash
php artisan test
php artisan test tests/Feature/PropertyApiTest.php
php artisan test --coverage
php artisan config:clear && php artisan test
```

### Database
```bash
php artisan migrate
php artisan migrate:refresh
php artisan tinker
php artisan list
```

### Testing Utilities
```bash
php test_scripts/create_admin.php
php test_scripts/create_sample_data.php
php test_scripts/test_notifications.php
php test_scripts/test_smtp_final.php
php test_scripts/test_admin_portal.php
```

### Scheduled Tasks (Required for Notifications)
Add to system cron (runs daily at 9 AM):
```bash
# Linux/Mac: crontab -e
* * * * * cd /path-to-efiewura && php artisan schedule:run >> /dev/null 2>&1

# Windows Task Scheduler: run every minute
php artisan schedule:run
```

## Architecture & Patterns

### Role-Based Access Control
- **Roles**: admin, landlord, tenant, user (in users.role)
- **Middleware**: CheckRole.php validates role, returns 403 if denied
- **Routes**: Use middleware('role:landlord') or middleware('role:admin')

### Soft Delete System (Critical)
**All deletions are soft deletes** — mark with status='deleted', deleted_at, deleted_by instead of hard delete.

- **Models**: User, Property, Booking, Notification use custom softDelete() and restore() methods
- **Query scopes**: notDeleted(), softDeleted(), withDeleted(), .active()
- **Why**: Audit trails, data recovery, compliance, admin visibility

Usage:
```php
$user->softDelete();
$user->restore();
$users = User::notDeleted()->get();
```

### Automated Notification System (Time-Based)

**Scheduler**: Runs daily at 9:00 AM via routes/console.php
```php
Schedule::command('notifications:check-due-dates')->dailyAt('09:00');
```

**Command** (app/Console/Commands/CheckDueDates.php) checks:
- Lease expiry: 60, 30, 7 days before move-out
- Payment reminders: 5 days advance, due date, 3 days overdue
- Move-in/out: 7 days, day-of
- Auto-release: Release overdue properties (landlord-configurable 7-365 days)

**Create notifications**: Use static factory methods:
```php
Notification::createLeaseExpiryReminder($booking, $days, 'tenant');
Notification::createPaymentReminder($booking, $type);
```

**Deduplication**: Checks existing by type, booking_id, data->reminder_days

**Email**: EmailNotificationService.php dispatches HTML emails. Views in resources/views/emails/

**Test mode**: php artisan notifications:check-due-dates --test (preview without creating)

### Booking Workflow (State Machine)

**States**: pending -> confirmed/rejected -> cancelled/completed

- Tenants request bookings (future dates, 1-60 months)
- Landlords confirm/reject pending bookings for their properties only
- Property auto-updates: occupied when confirmed, available when cancelled
- Notifications on state changes
- Stores full tenant info (name, phone, email, occupation, income, emergency contact)

### Model Relationships

```
User (1) -> (1) Landlord
User (1) -> (1) Tenant
User (1) -> (M) Booking
User (1) -> (M) Notification

Landlord (1) -> (M) Property
Landlord (1) -> (M) Booking

Property (1) -> (1) Landlord
Property (1) -> (M) Booking

Booking (M) -> (1) User
Booking (M) -> (1) Landlord
Booking (M) -> (1) Property
```

**Critical**: Use landlords.id, NOT users.id for landlord relations. Eager load to avoid N+1 queries:
```php
Booking::with(['user', 'landlord.user', 'property'])->get();
```

### API Response Format

All endpoints return:
```json
{
    "success": true|false,
    "message": "Human-readable message",
    "data": {}
}
```

**Status codes**: 200 (success), 201 (created), 401 (auth), 403 (forbidden), 422 (validation), 500 (error)

### Validation Pattern

Use Validator::make() in controllers:
```php
$validator = Validator::make($request->all(), [
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8',
]);

if ($validator->fails()) {
    return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validator->errors()
    ], 422);
}
```

## Project Structure

```
app/
├── Console/Commands/
│   ├── CheckDueDates.php (scheduled notification checks)
│   └── SendPendingEmailNotifications.php
├── Http/
│   ├── Controllers/API/
│   │   ├── AuthController.php (auth, profile, password)
│   │   ├── PropertyController.php (CRUD + ownership validation)
│   │   ├── BookingController.php (workflow)
│   │   ├── NotificationController.php (list, read/unread)
│   │   ├── AdminController.php (dashboard, analytics, management)
│   │   ├── AdminSettingsController.php (settings)
│   │   └── TwoFactorController.php (2FA)
│   └── Middleware/
│       ├── CheckRole.php
│       └── EnsureJsonApi.php
├── Models/
│   ├── User.php (role, 2FA, soft delete)
│   ├── Landlord.php (business_name, verified, overdue_release_days)
│   ├── Tenant.php
│   ├── Property.php (landlord_id, availability_status, amenities)
│   ├── Booking.php (state machine)
│   ├── Notification.php (type-based)
│   ├── PropertyCategory.php
│   ├── UserPreference.php
│   └── AdminSetting.php
└── Services/
    └── EmailNotificationService.php

routes/
├── api.php (all API endpoints)
├── web.php (public welcome)
└── console.php (scheduled commands)

database/
├── migrations/ (23+ files)
└── seeders/

config/
├── email_notifications.php (notification templates)
├── sanctum.php (API tokens)
└── auth.php

resources/views/emails/ (HTML templates)

tests/
├── Feature/ (API integration tests)
└── Unit/ (unit tests)

test_scripts/ (standalone utilities)
```

## Configuration

### .env Key Variables
```
APP_ENV=local|production
APP_DEBUG=true|false
DB_CONNECTION=sqlite|mysql
DB_DATABASE=database/database.sqlite or efiewura_v1
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
MAIL_MAILER=log|smtp
EMAIL_NOTIFICATIONS_ENABLED=true
```

### config/email_notifications.php
Defines email templates:
- welcome_message
- new_booking_request, booking_confirmed, booking_rejected
- lease_expiry_reminder
- payment_reminder_5_days, payment_due_today, payment_overdue_3_days
- move_in_reminder_7_days, move_in_today, move_out_reminder
- property_auto_released

Each has a Blade view (resources/views/emails/{type}.blade.php) with subject interpolation.

## Common Tasks

### Adding API Endpoint
1. Create method in app/Http/Controllers/API/{Controller}.php
2. Add route to routes/api.php with proper middleware
3. Validate with Validator::make(), return 422 on failure
4. Return JSON with success, message, data
5. Eager load: .with(['relations'])
6. Check soft deletes: .notDeleted() or .active()

### Adding Scheduled Notification Type
1. Add static method to app/Models/Notification.php
2. Add config to config/email_notifications.php
3. Create Blade view resources/views/emails/{type}.blade.php
4. Add check method to app/Console/Commands/CheckDueDates.php
5. Test: php artisan notifications:check-due-dates --test

### Modifying Database
1. php artisan make:migration migration_name
2. **Critical**: Add down() method for rollback
3. php artisan migrate
4. Test: php artisan migrate:refresh

## Key Features

### Two-Factor Authentication
- Optional per-user, can be admin-required
- TOTP (Google Authenticator) + recovery codes
- users.two_factor_secret (encrypted), two_factor_recovery_codes (encrypted array)
- Methods: hasTwoFactorAuthentication(), requiresTwoFactorAuthentication(), generateRecoveryCodes()

### Admin Portal
- Dashboard with real-time counts
- Analytics: 7/30/90-day trends (users, bookings, revenue)
- User management: create, edit, soft delete, restore
- Property moderation: view, moderate, status updates
- Booking management: view, update, notes
- Notification management: create, view delivery status
- Settings: platform configuration
- Two-factor enforcement

### Property Features
- Images as JSON array (first is main, accessible via $property->main_image)
- Amenities as JSON array
- Availability status tracking
- View count tracking

### Auto-Release Feature
- Landlords configure overdue_release_days (7-365 days, default 30)
- Command auto-releases overdue properties
- Sends property_auto_released notification
- Resets property to available

### Currency & Pricing
- Monetary fields: decimal:2 (price, monthly_rent, security_deposit, etc.)
- Currency codes separate (properties.currency, bookings.currency)
- Formatted accessor: $booking->formatted_monthly_rent

## Testing

### PHPUnit
- Test uses in-memory SQLite (no side effects)
- Test config in phpunit.xml
- Suites: Unit and Feature
- Coverage: app/ directory

### Test Scripts
Located in test_scripts/:
- Create test data
- Test API endpoints
- Verify notifications
- Test admin portal

## Important Guidelines

From .github/copilot-instructions.md:
- **Never hard delete**: Use soft delete pattern exclusively
- **Property ownership**: Validate landlord owns property
- **Eager loading**: Essential to prevent N+1 queries
- **Notification deduplication**: Check before creating
- **Date casting**: 'date' for date-only, 'datetime' for timestamps
- **Stateless API**: Sanctum tokens don't need sessions
- **Cron requirement**: Notifications require cron/Task Scheduler

## Common Gotchas

1. **Soft delete scopes**: Always use .notDeleted() or results include deleted records
2. **Landlord ID vs User ID**: Use landlord_id, NOT user_id
3. **Test scheduled commands**: Use --test flag before real execution
4. **Email template files**: Verify resources/views/emails/{type}.blade.php exists
5. **Eager loading**: Missing .with() causes N+1 queries
6. **Role middleware**: Case-sensitive, exact string match
7. **Migration order**: Timestamp order, foreign keys must reference existing tables
8. **Recovery codes**: One-time use; regenerate after use
9. **Property images**: First array element is main; validate existence
10. **Validation errors**: Return 422 with errors object, not 400
