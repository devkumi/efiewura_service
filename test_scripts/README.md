# Test Scripts Directory

This directory contains all testing and development utility scripts for the Efiewura platform.

## 📁 Directory Structure

### 🔧 **Setup & Data Creation Scripts**
- `create_admin.php` - Creates an admin user for testing admin portal functionality
- `create_new_landlord.php` - Creates test landlord credentials and sample data
- `create_sample_data.php` - Generates comprehensive sample data (users, properties, bookings)
- `create_properties_bookings.php` - Creates test properties and associated bookings
- `create_payment_test_booking.php` - Creates bookings specifically for payment testing
- `create_test_notification_booking.php` - Creates bookings to test notification system

### 📧 **Email & SMTP Testing Scripts**
- `smtp_diagnostics.php` - Diagnoses SMTP connection issues and configuration
- `test_smtp_email.php` - Basic SMTP email sending test
- `test_smtp_final.php` - Final comprehensive SMTP test with working configuration
- `test_quick_email.php` - Quick email delivery test
- `test_final_email.php` - Final email system validation
- `test_email_log.php` - Tests email logging functionality
- `test_email_notifications.php` - Tests email notification system
- `test_notification_emails.php` - Tests notification-triggered emails

### 🔔 **Notification System Tests**
- `test_notifications.php` - General notification system testing
- `test_time_based_notifications.php` - Tests scheduled and time-based notifications
- `test_admin_notification_read.php` - Tests admin notification read functionality (HTTP)
- `test_admin_notification_direct.php` - Tests admin notification read (direct Laravel)
- `test_admin_notification_server.php` - Tests admin notification via Laravel server
- `test_admin_bulk_read.php` - Tests bulk notification read functionality

### 🏠 **Feature-Specific Tests**
- `test_properties.php` - Tests property management functionality
- `test_bookings.php` - Tests booking system and workflows
- `test_admin_portal.php` - Tests admin portal endpoints and functionality
- `test_api.php` - General API endpoint testing
- `test_simple.php` - Simple system connectivity test

### 🐛 **Debug & Diagnostic Scripts**
- `debug_payment_logic.php` - Debugs payment processing logic and workflows

## 🚀 **How to Use**

### Running Individual Scripts
```bash
# Navigate to the project root
cd /c/xampp/htdocs/efiewura

# Run any test script
php test_scripts/[script_name].php
```

### Common Testing Workflows

#### 1. **Initial Setup Testing**
```bash
# Create admin user
php test_scripts/create_admin.php

# Create sample data
php test_scripts/create_sample_data.php

# Test basic connectivity
php test_scripts/test_simple.php
```

#### 2. **Email System Testing**
```bash
# Diagnose SMTP configuration
php test_scripts/smtp_diagnostics.php

# Test email delivery
php test_scripts/test_smtp_final.php

# Test notification emails
php test_scripts/test_notification_emails.php
```

#### 3. **Admin Portal Testing**
```bash
# Test admin functionality
php test_scripts/test_admin_portal.php

# Test admin notifications
php test_scripts/test_admin_notification_server.php

# Test bulk operations
php test_scripts/test_admin_bulk_read.php
```

#### 4. **API Testing**
```bash
# Test general API endpoints
php test_scripts/test_api.php

# Test specific features
php test_scripts/test_properties.php
php test_scripts/test_bookings.php
```

## 📋 **Test Categories**

### ✅ **Fully Working Tests**
- All SMTP/Email tests (using eyramfurniture.com configuration)
- Admin notification management tests
- Property and booking tests
- General API endpoint tests

### 🔧 **Setup Required**
- Some tests require Laravel development server: `php artisan serve --host=127.0.0.1 --port=8000`
- Email tests require valid SMTP credentials in `.env`
- Admin tests require admin user (created via `create_admin.php`)

## 🔗 **Dependencies**

### **Environment Variables Required:**
```env
# SMTP Configuration (for email tests)
MAIL_MAILER=smtp
MAIL_HOST=mail.eyramfurniture.com
MAIL_PORT=465
MAIL_USERNAME=your_email@eyramfurniture.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl

# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### **Laravel Services:**
- Laravel Sanctum (for API authentication)
- Laravel Mail (for email functionality)
- SQLite database
- Artisan commands

## 📊 **Test Results Tracking**

Each test script provides detailed output including:
- ✅ Success indicators
- ❌ Error messages with debugging info
- 📊 Performance metrics where applicable
- 🔍 Detailed response data for API tests

## 🔄 **Maintenance**

### **Adding New Tests**
1. Create new test file in this directory
2. Follow naming convention: `test_[feature_name].php`
3. Update this README with test description
4. Include proper error handling and output formatting

### **Cleaning Up**
```bash
# Remove all test data (if needed)
php artisan migrate:fresh --seed
```

---

**Last Updated:** July 29, 2025  
**Platform:** Efiewura Property Management System  
**Framework:** Laravel 11 with PHP 8.2+

## Comprehensive Soft Delete Testing

### test_comprehensive_soft_delete.php
**Purpose**: Tests the comprehensive soft delete functionality across all major models (Users, Properties, Bookings)

**What it tests**:
- User soft delete and restore functionality
- Property soft delete and restore functionality  
- Booking soft delete and restore functionality
- Query scopes (notDeleted, softDeleted, withDeleted)
- Relationships that automatically exclude deleted records
- Audit trail (deleted_by, deleted_at fields)

**Key features demonstrated**:
- `softDelete()` method that sets status to 'deleted' with audit trail
- `restore()` method that reactivates records
- `isDeleted()` check method
- `notDeleted()`, `softDeleted()`, `withDeleted()` query scopes
- Automatic exclusion of deleted records in relationships
- Admin audit trails showing who deleted what and when

**Usage**: `php test_scripts/test_comprehensive_soft_delete.php`

### debug_soft_delete.php
**Purpose**: Debug script for troubleshooting soft delete functionality

**Usage**: `php test_scripts/debug_soft_delete.php`

### test_firstname_lastname.php
**Purpose**: Tests the firstname/lastname functionality for user management

**What it tests**:
- Creating users with separate firstname and lastname fields
- Automatic generation of the name field from firstname + lastname
- Backward compatibility with existing name-only users
- Full name accessor for consistent name access
- Database structure verification

**Key features demonstrated**:
- `firstname` and `lastname` database fields
- Automatic `name` field population via model events
- `full_name` accessor attribute for getting complete name
- `full_name` mutator for setting firstname/lastname from full name
- Backward compatibility with existing users

**Usage**: `php test_scripts/test_firstname_lastname.php`
