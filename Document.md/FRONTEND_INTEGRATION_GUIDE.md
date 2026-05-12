# Frontend Integration Guide for User Preferences

## 🎯 Backend API is Ready!

The backend preferences functionality is **fully implemented and tested**. Here's how to integrate it with your frontend:

## 📡 Available API Endpoints

### 1. **GET** `/api/profile` - Get User Profile with Preferences
```javascript
const response = await fetch('/api/profile', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${authToken}`,
    'Accept': 'application/json'
  }
});

const userData = await response.json();
console.log(userData.data.preferences); // All preferences available
```

### 2. **PUT** `/api/profile` - Update Profile & Preferences
```javascript
const updateProfile = async (profileData) => {
  const response = await fetch('/api/profile', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${authToken}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(profileData)
  });
  
  return await response.json();
};
```

## 🔧 Frontend Implementation Examples

### 1. **Update Basic Profile Info**
```javascript
// Update firstname, lastname, bio
const updateBasicInfo = async () => {
  const result = await updateProfile({
    firstname: 'John',
    lastname: 'Doe',
    bio: 'Updated bio description'
  });
  
  if (result.success) {
    console.log('Profile updated:', result.data);
  }
};
```

### 2. **Update Display Preferences**
```javascript
// Update theme, timezone, date format
const updateDisplayPrefs = async () => {
  const result = await updateProfile({
    preferences: {
      theme: 'dark',
      timezone: 'Africa/Accra',
      date_format: 'YYYY-MM-DD',
      dashboard_refresh_interval: 60
    }
  });
  
  if (result.success) {
    console.log('Display preferences updated');
  }
};
```

### 3. **Update Notification Preferences**
```javascript
// Update notification settings
const updateNotificationPrefs = async () => {
  const result = await updateProfile({
    preferences: {
      notifications: {
        email: true,
        sms: false,
        new_bookings: true,
        property_updates: false,
        user_registrations: true,
        system_alerts: true,
        weekly_reports: false
      }
    }
  });
  
  if (result.success) {
    console.log('Notification preferences updated');
  }
};
```

### 4. **Update Everything at Once**
```javascript
// Update profile and all preferences in one call
const updateEverything = async () => {
  const result = await updateProfile({
    firstname: 'John',
    lastname: 'Doe',
    bio: 'System administrator with 5 years experience',
    preferences: {
      timezone: 'Africa/Lagos',
      date_format: 'DD/MM/YYYY',
      theme: 'dark',
      dashboard_refresh_interval: 45,
      notifications: {
        email: true,
        sms: true,
        new_bookings: true,
        property_updates: true,
        user_registrations: false,
        system_alerts: true,
        weekly_reports: true
      }
    }
  });
  
  if (result.success) {
    console.log('All preferences updated:', result.data.preferences);
  }
};
```

## 🎨 Vue.js Integration for Profile.vue

### Template Updates
```vue
<template>
  <div class="preferences-section">
    <!-- Basic Profile Info -->
    <div class="profile-info">
      <input 
        v-model="profileForm.firstname" 
        placeholder="First Name"
        @blur="updateProfile"
      />
      <input 
        v-model="profileForm.lastname" 
        placeholder="Last Name"
        @blur="updateProfile"
      />
      <textarea 
        v-model="profileForm.bio" 
        placeholder="Bio"
        @blur="updateProfile"
      ></textarea>
    </div>

    <!-- Display Preferences -->
    <div class="display-preferences">
      <select v-model="preferences.theme" @change="updatePreferences">
        <option value="light">Light Theme</option>
        <option value="dark">Dark Theme</option>
      </select>
      
      <select v-model="preferences.timezone" @change="updatePreferences">
        <option value="Africa/Lagos">Lagos</option>
        <option value="Africa/Accra">Accra</option>
        <option value="UTC">UTC</option>
      </select>
      
      <select v-model="preferences.date_format" @change="updatePreferences">
        <option value="DD/MM/YYYY">DD/MM/YYYY</option>
        <option value="MM/DD/YYYY">MM/DD/YYYY</option>
        <option value="YYYY-MM-DD">YYYY-MM-DD</option>
      </select>
      
      <input 
        type="number" 
        v-model="preferences.dashboard_refresh_interval" 
        min="10" 
        max="300"
        @change="updatePreferences"
      />
    </div>

    <!-- Notification Preferences -->
    <div class="notification-preferences">
      <label>
        <input 
          type="checkbox" 
          v-model="preferences.notifications.email"
          @change="updateNotificationPreferences"
        />
        Email Notifications
      </label>
      
      <label>
        <input 
          type="checkbox" 
          v-model="preferences.notifications.new_bookings"
          @change="updateNotificationPreferences"
        />
        New Bookings
      </label>
      
      <label>
        <input 
          type="checkbox" 
          v-model="preferences.notifications.property_updates"
          @change="updateNotificationPreferences"
        />
        Property Updates
      </label>
      
      <!-- Add more notification checkboxes as needed -->
    </div>
  </div>
</template>
```

### Script Implementation
```vue
<script>
export default {
  name: 'Profile',
  data() {
    return {
      loading: false,
      profileForm: {
        firstname: '',
        lastname: '',
        bio: ''
      },
      preferences: {
        timezone: 'Africa/Lagos',
        date_format: 'DD/MM/YYYY',
        theme: 'light',
        dashboard_refresh_interval: 30,
        notifications: {
          email: true,
          sms: false,
          new_bookings: true,
          property_updates: true,
          user_registrations: false,
          system_alerts: true,
          weekly_reports: false
        }
      }
    };
  },
  
  async mounted() {
    await this.loadProfile();
  },
  
  methods: {
    async loadProfile() {
      try {
        const response = await fetch('/api/profile', {
          headers: {
            'Authorization': `Bearer ${this.$store.state.auth.token}`,
            'Accept': 'application/json'
          }
        });
        
        const result = await response.json();
        
        if (result.success) {
          const user = result.data;
          
          // Load basic profile
          this.profileForm.firstname = user.firstname;
          this.profileForm.lastname = user.lastname;
          this.profileForm.bio = user.preferences?.bio || '';
          
          // Load preferences
          if (user.preferences) {
            this.preferences = {
              timezone: user.preferences.timezone,
              date_format: user.preferences.date_format,
              theme: user.preferences.theme,
              dashboard_refresh_interval: user.preferences.dashboard_refresh_interval,
              notifications: {
                email: user.preferences.email_notifications,
                sms: user.preferences.sms_notifications,
                new_bookings: user.preferences.new_bookings,
                property_updates: user.preferences.property_updates,
                user_registrations: user.preferences.user_registrations,
                system_alerts: user.preferences.system_alerts,
                weekly_reports: user.preferences.weekly_reports
              }
            };
          }
        }
      } catch (error) {
        console.error('Error loading profile:', error);
      }
    },
    
    async updateProfile() {
      await this.saveToAPI({
        firstname: this.profileForm.firstname,
        lastname: this.profileForm.lastname,
        bio: this.profileForm.bio
      });
    },
    
    async updatePreferences() {
      await this.saveToAPI({
        preferences: {
          timezone: this.preferences.timezone,
          date_format: this.preferences.date_format,
          theme: this.preferences.theme,
          dashboard_refresh_interval: this.preferences.dashboard_refresh_interval
        }
      });
    },
    
    async updateNotificationPreferences() {
      await this.saveToAPI({
        preferences: {
          notifications: this.preferences.notifications
        }
      });
    },
    
    async saveToAPI(data) {
      if (this.loading) return;
      
      this.loading = true;
      
      try {
        const response = await fetch('/api/profile', {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${this.$store.state.auth.token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
          this.$toast.success('Preferences updated successfully');
          
          // Update local data with response
          if (result.data.preferences) {
            // Refresh preferences from response
            await this.loadProfile();
          }
        } else {
          this.$toast.error(result.message || 'Update failed');
          
          if (result.errors) {
            console.error('Validation errors:', result.errors);
          }
        }
      } catch (error) {
        console.error('Error updating preferences:', error);
        this.$toast.error('Network error occurred');
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
```

## 🎯 Key Integration Points

### 1. **Authentication**
Always include the Bearer token:
```javascript
headers: {
  'Authorization': `Bearer ${authToken}`,
  'Accept': 'application/json'
}
```

### 2. **Response Structure**
The backend returns preferences in this structure:
```javascript
response.data.preferences = {
  timezone: 'Africa/Lagos',
  theme: 'light',
  email_notifications: true,  // Note: snake_case in response
  new_bookings: true,
  // ... other preferences
}
```

### 3. **Request Structure**
Send preferences updates in this nested format:
```javascript
{
  preferences: {
    notifications: {
      email: true,
      new_bookings: false
    }
  }
}
```

### 4. **Validation**
The backend validates:
- `theme`: 'light' or 'dark'
- `date_format`: 'DD/MM/YYYY', 'MM/DD/YYYY', or 'YYYY-MM-DD'
- `dashboard_refresh_interval`: 10-300 seconds
- All notification booleans

### 5. **Default Values**
New users get these defaults automatically:
- Timezone: 'Africa/Lagos'
- Theme: 'light'
- Date format: 'DD/MM/YYYY'
- Dashboard refresh: 30 seconds
- Email notifications: enabled
- Most other notifications: enabled

## 🚀 Next Steps

1. **Replace your current disabled preferences code** with the API calls above
2. **Test the integration** with the working backend
3. **Add error handling** for validation failures
4. **Implement toast notifications** for user feedback
5. **Add loading states** for better UX

The backend is ready and tested - you can start integrating immediately!
