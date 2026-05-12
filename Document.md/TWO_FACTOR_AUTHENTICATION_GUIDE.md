# Two-Factor Authentication (2FA) Implementation Guide

## Overview

The Efiewura platform now includes a comprehensive Two-Factor Authentication (2FA) system that provides an additional layer of security for user accounts. This implementation uses Time-based One-Time Passwords (TOTP) compatible with popular authenticator apps like Google Authenticator, Authy, and Microsoft Authenticator.

## Features

### ✅ Core 2FA Features
- **QR Code Setup**: Users can scan QR codes to set up 2FA on their mobile devices
- **Manual Entry**: Alternative setup method using secret keys
- **TOTP Verification**: Standard 6-digit time-based codes
- **Recovery Codes**: 10 single-use backup codes for account recovery
- **Admin Controls**: Administrators can enforce 2FA requirements
- **Optional vs Required**: Flexible enforcement per user

### ✅ Security Features
- **Encrypted Storage**: 2FA secrets are encrypted in the database
- **Hashed Recovery Codes**: Recovery codes are hashed for security
- **Session Management**: Proper token handling after 2FA verification
- **Audit Trail**: Tracking of 2FA setup and usage

## API Endpoints

### User 2FA Management

#### 1. Generate 2FA Secret
```
POST /api/2fa/generate-secret
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "2FA secret generated successfully",
  "data": {
    "secret": "ABCDEFGHIJKLMNOP",
    "qr_code_url": "data:image/png;base64,iVBORw0KGgoA...",
    "manual_entry_key": "ABCD EFGH IJKL MNOP",
    "backup_codes": ["ABC12345", "DEF67890", ...]
  }
}
```

#### 2. Confirm 2FA Setup
```
POST /api/2fa/confirm
Authorization: Bearer {token}

{
  "code": "123456"
}
```

#### 3. Verify 2FA Code
```
POST /api/2fa/verify
Authorization: Bearer {token}

{
  "code": "123456"
}
```

#### 4. Get Recovery Codes
```
GET /api/2fa/recovery-codes
Authorization: Bearer {token}
```

#### 5. Regenerate Recovery Codes
```
POST /api/2fa/recovery-codes/regenerate
Authorization: Bearer {token}
```

#### 6. Disable 2FA
```
DELETE /api/2fa/disable
Authorization: Bearer {token}
```

### Enhanced Login with 2FA

#### Standard Login (with 2FA support)
```
POST /api/login

{
  "email": "user@example.com",
  "password": "password",
  "two_factor_code": "123456",    // Optional: 6-digit TOTP code
  "recovery_code": "ABC12345"     // Optional: recovery code
}
```

**Response when 2FA is required:**
```json
{
  "success": false,
  "message": "Two-factor authentication required",
  "requires_2fa": true,
  "two_factor_method": "app"
}
```

**Response when login is successful:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "bearer_token",
    "token_type": "Bearer"
  }
}
```

### Admin 2FA Management

#### 1. Get User 2FA Status
```
GET /api/admin/2fa/users/{user_id}/status
Authorization: Bearer {admin_token}
```

#### 2. Toggle 2FA Requirement for User
```
PATCH /api/admin/2fa/users/{user_id}/toggle-requirement
Authorization: Bearer {admin_token}
```

#### 3. Force Disable User's 2FA
```
DELETE /api/admin/2fa/users/{user_id}/disable
Authorization: Bearer {admin_token}
```

#### 4. Get 2FA Overview
```
GET /api/admin/2fa/overview
Authorization: Bearer {admin_token}
```

## Frontend Integration

### 1. Setting Up 2FA

```javascript
// Step 1: Generate secret and QR code
const setupResponse = await fetch('/api/2fa/generate-secret', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`,
    'Content-Type': 'application/json'
  }
});

const setup = await setupResponse.json();

// Step 2: Display QR code to user
document.getElementById('qr-code').src = setup.data.qr_code_url;
document.getElementById('manual-key').textContent = setup.data.manual_entry_key;

// Step 3: User enters code from their authenticator app
const confirmCode = prompt('Enter the 6-digit code from your authenticator app:');

const confirmResponse = await fetch('/api/2fa/confirm', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ code: confirmCode })
});

if (confirmResponse.ok) {
  alert('2FA setup complete!');
  // Display recovery codes to user
  const confirm = await confirmResponse.json();
  console.log('Recovery codes:', confirm.data.recovery_codes);
}
```

### 2. Login with 2FA

```javascript
async function loginWithCredentials(email, password, twoFactorCode = null) {
  const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      email,
      password,
      two_factor_code: twoFactorCode
    })
  });

  const result = await response.json();

  if (result.requires_2fa) {
    // Show 2FA input form
    const code = prompt('Enter your 2FA code:');
    return loginWithCredentials(email, password, code);
  }

  if (result.success) {
    // Store token and redirect
    localStorage.setItem('token', result.data.token);
    window.location.href = '/dashboard';
  }
}
```

### 3. Recovery Code Login

```javascript
async function loginWithRecoveryCode(email, password, recoveryCode) {
  const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      email,
      password,
      recovery_code: recoveryCode
    })
  });

  return await response.json();
}
```

## Mobile App Integration

### Authenticator App Setup

1. **QR Code Method**: Users scan the QR code with their authenticator app
2. **Manual Entry**: Users manually enter the secret key

### Compatible Apps
- Google Authenticator
- Authy
- Microsoft Authenticator
- 1Password
- LastPass Authenticator
- Any TOTP-compatible app

### QR Code Format
```
otpauth://totp/Efiewura:user@example.com?secret=SECRETKEY&issuer=Efiewura&algorithm=SHA1&digits=6&period=30
```

## Database Schema

### New Fields in `users` Table

```sql
ALTER TABLE users ADD COLUMN two_factor_enabled BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN two_factor_required BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULL;
ALTER TABLE users ADD COLUMN two_factor_recovery_codes TEXT NULL;
ALTER TABLE users ADD COLUMN two_factor_confirmed_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN two_factor_method VARCHAR(255) DEFAULT 'app';
ALTER TABLE users ADD COLUMN two_factor_phone VARCHAR(255) NULL;
```

## Security Considerations

### Best Practices Implemented

1. **Secret Encryption**: 2FA secrets are encrypted using Laravel's encryption
2. **Recovery Code Hashing**: Recovery codes are hashed before storage
3. **Single-Use Recovery Codes**: Each recovery code can only be used once
4. **Time Window Validation**: TOTP codes have a 30-second validity window
5. **Rate Limiting**: Implement rate limiting on 2FA verification endpoints

### Additional Security Measures

1. **Account Lockout**: Consider implementing temporary lockout after multiple failed 2FA attempts
2. **Audit Logging**: Log all 2FA-related activities
3. **Backup Methods**: Recovery codes provide backup access
4. **Admin Override**: Admins can disable 2FA for locked-out users

## Testing

### Test Script Usage

Run the 2FA functionality tests:

```bash
cd /path/to/efiewura
php test_scripts/test_2fa_simple.php
```

This will verify:
- Google2FA package functionality
- QR code generation
- TOTP verification
- Recovery code handling
- User model 2FA methods

### Manual Testing Steps

1. **Setup Test**:
   - Register a new user
   - Generate 2FA secret
   - Scan QR code with authenticator app
   - Confirm setup with generated code

2. **Login Test**:
   - Logout and attempt login
   - Verify 2FA code is required
   - Login with TOTP code
   - Test recovery code login

3. **Admin Test**:
   - Login as admin
   - Toggle 2FA requirement for user
   - Force disable user's 2FA
   - View 2FA overview statistics

## Troubleshooting

### Common Issues

1. **QR Code Not Displaying**:
   - **GD Extension Error**: If you get "Unable to generate image: please check if the GD extension is enabled", see `GD_EXTENSION_FIX_GUIDE.md`
   - **Solution**: Enable GD extension in php.ini or use the fallback external QR service
   - Check that the Endroid QR Code package is installed
   - Verify base64 image format in response

2. **Invalid TOTP Codes**:
   - Check device time synchronization
   - Verify secret key accuracy
   - Ensure 30-second time window alignment

3. **Recovery Codes Not Working**:
   - Verify codes haven't been used already
   - Check for typos in code entry
   - Ensure proper hashing/comparison

4. **Database Errors**:
   - Verify migration has been run
   - Check encryption key is set properly
   - Ensure proper field types in database

### GD Extension Setup

For optimal QR code generation, ensure the GD extension is enabled:

```bash
# Check if GD is enabled
php -r "echo extension_loaded('gd') ? 'GD is loaded' : 'GD is NOT loaded';"

# Enable in php.ini (XAMPP)
# Uncomment: extension=gd
# Restart Apache
```

The system includes fallback QR code generation using external services when GD is not available.

### Debug Commands

```bash
# Check 2FA status for a user
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->hasTwoFactorAuthentication();

# Generate test TOTP code
>>> $google2fa = new PragmaRX\Google2FA\Google2FA();
>>> $code = $google2fa->getCurrentOtp('SECRETKEY');

# Verify migration status
php artisan migrate:status
```

## Future Enhancements

### Potential Improvements

1. **SMS 2FA**: Add SMS-based 2FA as an alternative
2. **Hardware Keys**: Support for FIDO2/WebAuthn hardware keys
3. **Trusted Devices**: Remember trusted devices for a period
4. **2FA Backup Email**: Email-based backup codes
5. **Enhanced Admin Dashboard**: Detailed 2FA analytics and management

### Configuration Options

Consider adding these configuration options:

```php
// config/auth.php
'two_factor' => [
    'enabled' => true,
    'required_for_admins' => true,
    'grace_period_days' => 7,
    'recovery_codes_count' => 10,
    'totp_window' => 1, // Allow 1 window before/after current
]
```

## Conclusion

The 2FA implementation provides robust security enhancement for the Efiewura platform while maintaining user-friendly setup and usage. The system is fully integrated with the existing authentication flow and provides comprehensive admin controls for security management.

For additional support or questions, refer to the test scripts and controller implementations for detailed usage examples.
