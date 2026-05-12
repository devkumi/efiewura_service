# Code Improvements

This document records every issue found in the AI-generated codebase and the changes made to address them.

---

## 1. Eliminated soft-delete duplication via a shared trait

**Problem:** `User`, `Property`, and `Booking` each contained the same six methods verbatim:
`softDelete()`, `restore()`, `isDeleted()`, `scopeNotDeleted()`, `scopeSoftDeleted()`, `scopeWithDeleted()`.
That is ~50 lines of identical code copied three times — any bug fix or behaviour change had to be applied in three places.

**Fix:** Created `app/Traits/SoftDeletable.php` with the shared implementation. Each model now uses `SoftDeletable` and overrides only what differs:

| Model | Override reason |
|-------|----------------|
| `Booking` | `restore()` must reset to `'pending'`, not `'active'` (workflow state) |
| `Property` | `softDelete()` / `restore()` must also toggle `is_active` |

**Files changed:** `app/Traits/SoftDeletable.php` (new), `app/Models/User.php`, `app/Models/Booking.php`, `app/Models/Property.php`

---

## 2. Removed unused imports and dead code in User model

**Problem:** `User::boot()` contained only a stale comment and a `parent::boot()` call — it did nothing. The `Auth` facade import was also left in after the soft-delete methods were extracted.

**Fix:** Deleted the empty `boot()` method. Removed the `use Illuminate\Support\Facades\Auth` import.

**File changed:** `app/Models/User.php`

---

## 3. Fixed insecure recovery code generation

**Problem:** `generateRecoveryCodes()` used `str_shuffle()` — a non-cryptographically-secure function — to produce 2FA recovery codes. An attacker who observed one code could significantly reduce the search space.

```php
// Before — predictable entropy
$codes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
```

**Fix:** Replaced with `Str::random()`, which internally calls `random_bytes()` (CSPRNG):

```php
// After — cryptographically secure
$codes[] = strtoupper(\Illuminate\Support\Str::random(8));
```

**File changed:** `app/Models/User.php`

---

## 4. Fixed N+1 queries in booking summary counts

**Problem:** `BookingController::myBookings()` and `landlordBookings()` each fired **3 additional queries** after pagination to build the `summary` block:

```php
// 3 separate COUNT(*) queries
'total_bookings'     => Auth::user()->bookings()->count(),
'pending_bookings'   => Auth::user()->bookings()->pending()->count(),
'confirmed_bookings' => Auth::user()->bookings()->confirmed()->count(),
```

**Fix:** Replaced with a single aggregate query using conditional sums:

```php
$counts = Auth::user()->bookings()
    ->selectRaw('COUNT(*) as total, SUM(status = "pending") as pending, SUM(status = "confirmed") as confirmed')
    ->first();
```

Both methods now use one query instead of three.

**File changed:** `app/Http/Controllers/API/BookingController.php`

---

## 5. Fixed `storeTestBooking()` bypassing the notification factory

**Problem:** `storeTestBooking()` created its notification via raw `Notification::create([...])` with a hand-rolled payload, diverging from the `Notification::createNewBookingRequest($booking)` factory used by the real `store()` method. This meant the test booking generated a different (and incomplete) notification structure, and any future changes to the factory would not be reflected in test bookings.

**Fix:** Replaced the raw create with the shared factory method:

```php
Notification::createNewBookingRequest($booking);
```

**File changed:** `app/Http/Controllers/API/BookingController.php`

---

## 6. Fixed inconsistent controller inheritance

**Problem:** `PropertyController` and `BookingController` extended `Illuminate\Routing\Controller as BaseController` directly, while every other controller in the project extends `App\Http\Controllers\Controller`. This inconsistency means any shared behaviour added to the base `Controller` class would silently not apply to these two controllers.

**Fix:** Both controllers now extend `App\Http\Controllers\Controller`.

**Files changed:** `app/Http/Controllers/API/PropertyController.php`, `app/Http/Controllers/API/BookingController.php`

---

## 7. Eliminated duplicate preferences formatting in AuthController

**Problem:** `profile()` and `updateProfile()` both contained an identical 14-line block that mapped `$user->preferences` attributes into a structured array. Any change to the preferences schema required updating both places.

**Fix:** Extracted into a private `formatUserData(User $user): array` method called from both endpoints.

**File changed:** `app/Http/Controllers/API/AuthController.php`

---

## Known architectural issues (not changed)

These are design decisions baked into the original schema that would require a migration to fix. Documented here for awareness.

### Booking `status` doubles as both workflow state and soft-delete flag

The `bookings.status` column holds `pending | confirmed | rejected | cancelled | deleted`. Calling `$booking->softDelete()` overwrites the booking's real workflow status with `'deleted'`, making it impossible to know what state the booking was in before deletion without additional columns. A cleaner design would use a separate `deleted_at` column (Laravel's built-in `SoftDeletes` trait) or a dedicated `is_deleted` boolean, leaving `status` for workflow state only.

### Hardcoded system stats in AdminController dashboard

`AdminController::dashboard()` returns `'uptime' => '99.8%'`, `'storage_used' => '78%'`, and `'last_backup'` set to 3 hours ago via `Carbon::now()->subHours(3)`. These are fabricated values, not real metrics. They will mislead operators. These should be removed or replaced with actual system calls.

### Exception messages exposed in 500 responses

Controllers return `'error' => $e->getMessage()` in 500 responses. In production this can leak stack details, file paths, SQL queries, or credentials. The message should only be included in non-production environments:

```php
'error' => app()->environment('production') ? 'Internal server error' : $e->getMessage()
```
