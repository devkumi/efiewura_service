# 2FA Disable Test Commands

## Step 1: Login with current 2FA code (replace XXXXXX with your current 6-digit code)

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jhaykhoma@gmail.com",
    "password": "123456789",
    "two_factor_code": "XXXXXX"
  }'
```

Copy the token from the response, then use it in Step 2.

## Step 2: Disable 2FA (replace YOUR_TOKEN_HERE with the token from Step 1)

```bash
curl -X POST http://127.0.0.1:8000/api/2fa/disable \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 42|dytmsuRF3o7Q76VzJf1TU82CiKRTq0SyXfJGkAGH95df9319" \
  -d '{
    "password": "123456789"
  }'
```

## Alternative: Check logs for debugging

After running the disable command, check the Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

This will show you exactly what data the server is receiving.
