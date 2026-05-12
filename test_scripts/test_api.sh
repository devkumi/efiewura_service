#!/bin/bash

# Test script for Efiewura API
BASE_URL="http://127.0.0.1:8000/api"

echo "=== Testing Efiewura API ==="
echo ""

# Test 1: Register a Landlord
echo "1. Testing Landlord Registration..."
LANDLORD_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Property Owner",
    "email": "landlord@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "landlord",
    "business_name": "Johns Properties Ltd",
    "phone": "+233244123456",
    "city": "Accra",
    "country": "Ghana"
  }')

echo "Response: $LANDLORD_RESPONSE"
echo ""

# Extract token from response (basic extraction)
LANDLORD_TOKEN=$(echo $LANDLORD_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo "Landlord Token: $LANDLORD_TOKEN"
echo ""

# Test 2: Register a Tenant
echo "2. Testing Tenant Registration..."
TENANT_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Tenant",
    "email": "tenant@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "tenant",
    "phone": "+233244654321",
    "date_of_birth": "1990-01-15",
    "gender": "female",
    "occupation": "Software Engineer",
    "monthly_income": 5000.00
  }')

echo "Response: $TENANT_RESPONSE"
echo ""

# Extract token from response
TENANT_TOKEN=$(echo $TENANT_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo "Tenant Token: $TENANT_TOKEN"
echo ""

# Test 3: Login as Landlord
echo "3. Testing Landlord Login..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "landlord@test.com",
    "password": "password123"
  }')

echo "Response: $LOGIN_RESPONSE"
echo ""

# Test 4: Get Landlord Profile
if [ ! -z "$LANDLORD_TOKEN" ]; then
  echo "4. Testing Get Landlord Profile..."
  PROFILE_RESPONSE=$(curl -s -X GET "$BASE_URL/profile" \
    -H "Authorization: Bearer $LANDLORD_TOKEN")
  
  echo "Response: $PROFILE_RESPONSE"
  echo ""
fi

# Test 5: Get Tenant Profile
if [ ! -z "$TENANT_TOKEN" ]; then
  echo "5. Testing Get Tenant Profile..."
  TENANT_PROFILE_RESPONSE=$(curl -s -X GET "$BASE_URL/profile" \
    -H "Authorization: Bearer $TENANT_TOKEN")
  
  echo "Response: $TENANT_PROFILE_RESPONSE"
  echo ""
fi

# Test 6: Test Role-based Access (Tenant trying to access Landlord route)
if [ ! -z "$TENANT_TOKEN" ]; then
  echo "6. Testing Role-based Access Control (should fail)..."
  ACCESS_RESPONSE=$(curl -s -X GET "$BASE_URL/landlord/test" \
    -H "Authorization: Bearer $TENANT_TOKEN")
  
  echo "Response: $ACCESS_RESPONSE"
  echo ""
fi

echo "=== API Testing Complete ==="
