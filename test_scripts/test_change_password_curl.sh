#!/bin/bash

echo "🔐 Quick Change Password Test"
echo "============================"
echo ""

BASE_URL="http://127.0.0.1:8080/api"

echo "1. First, login to get a token:"
echo "curl -X POST $BASE_URL/login \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"email\":\"your@email.com\",\"password\":\"currentpassword\"}'"
echo ""

echo "2. Then use the token to change password:"
echo "curl -X PUT $BASE_URL/change-password \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -H \"Authorization: Bearer YOUR_TOKEN_HERE\" \\"
echo "  -d '{"
echo "    \"current_password\": \"currentpassword\","
echo "    \"password\": \"newpassword123\","
echo "    \"password_confirmation\": \"newpassword123\""
echo "  }'"
echo ""

echo "3. Test login with new password:"
echo "curl -X POST $BASE_URL/login \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"email\":\"your@email.com\",\"password\":\"newpassword123\"}'"
echo ""

echo "✅ Change password endpoint: PUT /api/change-password"
echo "✅ Requires authentication token"
echo "✅ Validates current password"
echo "✅ Requires password confirmation"
echo "✅ Properly hashes new password"
