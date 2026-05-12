#!/bin/bash

echo "🌐 Testing API Registration with Firstname/Lastname"
echo "=================================================="
echo ""

# Base URL
BASE_URL="http://localhost/efiewura/public/api"

echo "1. Testing registration with firstname/lastname..."

# Test registration with firstname/lastname
curl -X POST "${BASE_URL}/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "firstname": "API",
    "lastname": "Test User",
    "email": "api.test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "tenant",
    "phone": "+233123456789"
  }' | python -m json.tool

echo ""
echo "2. Testing registration with legacy name field..."

# Test registration with legacy name
curl -X POST "${BASE_URL}/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Legacy User",
    "email": "legacy.test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "landlord",
    "business_name": "Test Business",
    "phone": "+233987654321"
  }' | python -m json.tool

echo ""
echo "3. Testing login with firstname/lastname user..."

# Test login
curl -X POST "${BASE_URL}/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "api.test@example.com",
    "password": "password123"
  }' | python -m json.tool

echo ""
echo "4. Testing login with legacy name user..."

# Test login
curl -X POST "${BASE_URL}/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "legacy.test@example.com",
    "password": "password123"
  }' | python -m json.tool

echo ""
echo "✅ API registration tests completed!"
