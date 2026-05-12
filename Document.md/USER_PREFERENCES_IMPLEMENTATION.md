# User ### Database Schema
**Table:** `user_preferences`
```sql
- id (primary key)
- user_id (foreign key to users table)
- timezone (string, default: 'Africa/Lagos')
- date_format (enum: 'DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD')
- theme (enum: 'light', 'dark')
- dashboard_refresh_interval (integer, 10-300 seconds)
- email_notifications (boolean)
- sms_notifications (boolean)
- new_bookings (boolean)
- property_updates (boolean)  
- user_registrations (boolean)
- system_alerts (boolean)
- weekly_reports (boolean)
- bio (nullable text, max 1000 characters)
- timestamps
```
*Note: Phone field removed - use role-specific profiles (landlord/tenant tables) for phone numbers*Implementation Summary

## Overview
The user preferences backend functionality has been successfully implemented and tested. The system now supports comprehensive user preference management through the existing profile API endpoints.

## Implementation Details

### 1. Database Schema
**Table:** `user_preferences`
```sql
- id (primary key)
- user_id (foreign key to users table)
- timezone (string, default: 'Africa/Lagos')
- date_format (enum: 'DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD')
- theme (enum: 'light', 'dark')
- dashboard_refresh_interval (integer, 10-300 seconds)
- email_notifications (boolean)
- sms_notifications (boolean)
- new_bookings (boolean)
- property_updates (boolean)  
- user_registrations (boolean)
- system_alerts (boolean)
- weekly_reports (boolean)
- phone (nullable string)
- bio (nullable text, max 1000 characters)
- timestamps
```

### 2. Model Implementation
**File:** `app/Models/UserPreference.php`
- Mass assignable fields for all preference categories
- Boolean casting for notification preferences
- Relationship with User model
- Helper methods for structured data access

### 3. API Endpoints Enhanced

#### GET /api/profile
**Response includes preferences:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "firstname": "John",
        "lastname": "Doe",
        "email": "john@example.com",
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
            "bio": null
        }
    }
}
```

#### PUT /api/profile
**Accepts preferences in request:**
```json
{
    "preferences": {
        "timezone": "Africa/Accra",
        "theme": "dark",
        "notifications": {
            "email": true,
            "sms": true,
            "new_bookings": true,
            "property_updates": false,
            "system_alerts": true,
            "weekly_reports": true
        }
    },
    "bio": "User bio description"
}
```

### 4. Validation Rules
- **timezone**: string, max 50 characters
- **date_format**: enum ('DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD')
- **theme**: enum ('light', 'dark')
- **dashboard_refresh_interval**: integer, 10-300 seconds
- **notifications**: nested boolean values
- **bio**: string, max 1000 characters

### 5. Default Preferences
New users automatically get default preferences:
- Timezone: Africa/Lagos
- Date format: DD/MM/YYYY
- Theme: light
- Dashboard refresh: 30 seconds
- Email notifications: enabled
- SMS notifications: disabled
- New bookings: enabled
- Property updates: enabled
- User registrations: disabled (admin only)
- System alerts: enabled
- Weekly reports: disabled

## Features Implemented ✅

### Notification Preferences
- Email notifications toggle
- SMS notifications toggle  
- New bookings notifications
- Property updates notifications
- User registrations notifications (admin)
- System alerts notifications
- Weekly reports notifications

### Display Preferences
- Timezone selection
- Date format preference
- Theme (light/dark mode)
- Dashboard auto-refresh interval

### Profile Information
- Bio/description field (max 1000 chars)

*Note: Phone numbers are stored in role-specific profiles (landlord/tenant tables), not in preferences*

### System Features
- Automatic default preferences creation
- Seamless integration with existing profile endpoint
- Comprehensive validation
- Nested JSON structure support
- Database relationship integrity

## Testing Results ✅

All test scenarios passed:
1. ✅ Profile retrieval with default preferences
2. ✅ Preferences-only updates
3. ✅ Profile info updates with bio
4. ✅ Validation with invalid data
5. ✅ Preferences structure verification
6. ✅ Default preferences for new users

## Usage Examples

### Frontend Integration
The backend now supports the exact structure requested:

```javascript
// Update user preferences
const updatePreferences = async () => {
  const response = await fetch('/api/profile', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      preferences: {
        timezone: 'Africa/Accra',
        theme: 'dark',
        dashboard_refresh_interval: 60,
        notifications: {
          email: true,
          sms: false,
          new_bookings: true,
          property_updates: false,
          system_alerts: true,
          weekly_reports: true
        }
      },
      bio: 'Updated user bio'
    })
  });
  
  const data = await response.json();
  return data.data.preferences;
};
```

## Migration Status
- ✅ Migration created: `2025_08_02_091817_create_user_preferences_table`
- ✅ Migration executed successfully
- ✅ All database constraints in place

## Next Steps for Frontend
1. Update preferences UI components to use new endpoints
2. Implement preference categories as designed
3. Add validation feedback for invalid preferences
4. Test theme switching and timezone handling
5. Implement dashboard refresh functionality

The backend preferences system is now complete and ready for frontend integration!
