<?php

echo "=== Testing Admin Notification Soft Delete Functionality ===\n";

// Test the soft delete and restore functionality
$loginUrl = "http://127.0.0.1:8000/api/register";

$adminData = [
    'name' => 'Test Admin ' . time(),
    'email' => 'admin' . time() . '@test.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'admin'
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $loginUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($adminData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$loginData = json_decode($response, true);
$token = $loginData['data']['token'];
$userId = $loginData['data']['user']['id'];

echo "✓ Created admin and got token\n";

// Create a test notification
$createNotificationUrl = "http://127.0.0.1:8000/api/admin/notifications";
$notificationData = [
    'type' => 'test',
    'title' => 'Test Soft Delete Notification',
    'message' => 'This notification will be soft deleted',
    'priority' => 'medium',
    'target_users' => [$userId],
    'send_email' => false
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $createNotificationUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($notificationData),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$notificationResponse = json_decode($response, true);
$notificationId = $notificationResponse['data']['notifications'][0]['id'];

echo "✓ Created notification with ID: {$notificationId}\n";

// Test 1: View active notifications (should show the notification)
echo "\n=== Test 1: View Active Notifications ===\n";
$activeUrl = "http://127.0.0.1:8000/api/admin/notifications?status=active";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $activeUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$data = json_decode($response, true);
$activeCount = $data['data']['total'] ?? 0;
echo "Active notifications count: {$activeCount}\n";

// Test 2: Soft delete the notification
echo "\n=== Test 2: Soft Delete Notification ===\n";
$deleteUrl = "http://127.0.0.1:8000/api/admin/notifications/{$notificationId}";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $deleteUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "Delete response code: {$httpCode}\n";
echo "Response: {$response}\n";

$deleteData = json_decode($response, true);
if ($deleteData && ($deleteData['success'] ?? false)) {
    echo "✅ SUCCESS: Notification soft deleted!\n";
    echo "✅ Status: " . ($deleteData['data']['status'] ?? 'unknown') . "\n";
    echo "✅ Deleted by: " . ($deleteData['data']['deleted_by'] ?? 'unknown') . "\n";
} else {
    echo "❌ FAILED to soft delete notification\n";
}

// Test 3: View active notifications again (should not show the deleted notification)
echo "\n=== Test 3: View Active Notifications After Delete ===\n";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $activeUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
$newActiveCount = $data['data']['total'] ?? 0;
echo "Active notifications count after delete: {$newActiveCount}\n";

if ($newActiveCount < $activeCount) {
    echo "✅ SUCCESS: Notification no longer appears in active list\n";
} else {
    echo "❌ FAILED: Notification still appears in active list\n";
}

// Test 4: View deleted notifications (should show the deleted notification)
echo "\n=== Test 4: View Deleted Notifications ===\n";
$deletedUrl = "http://127.0.0.1:8000/api/admin/notifications?status=deleted";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $deletedUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
$deletedCount = $data['data']['total'] ?? 0;
echo "Deleted notifications count: {$deletedCount}\n";

// Find our notification in deleted list
$foundDeleted = false;
if (isset($data['data']['data'])) {
    foreach ($data['data']['data'] as $notification) {
        if ($notification['id'] == $notificationId) {
            $foundDeleted = true;
            echo "✅ SUCCESS: Found notification in deleted list\n";
            echo "✅ Status: " . ($notification['status'] ?? 'unknown') . "\n";
            echo "✅ Deleted at: " . ($notification['deleted_at'] ?? 'unknown') . "\n";
            break;
        }
    }
}

if (!$foundDeleted) {
    echo "❌ FAILED: Notification not found in deleted list\n";
}

// Test 5: Restore the notification
echo "\n=== Test 5: Restore Notification ===\n";
$restoreUrl = "http://127.0.0.1:8000/api/admin/notifications/{$notificationId}/restore";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $restoreUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "Restore response code: {$httpCode}\n";
echo "Response: {$response}\n";

$restoreData = json_decode($response, true);
if ($restoreData && ($restoreData['success'] ?? false)) {
    echo "✅ SUCCESS: Notification restored!\n";
    echo "✅ Status: " . ($restoreData['data']['status'] ?? 'unknown') . "\n";
} else {
    echo "❌ FAILED to restore notification\n";
}

// Test 6: Verify notification is back in active list
echo "\n=== Test 6: Verify Restoration ===\n";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $activeUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
$finalActiveCount = $data['data']['total'] ?? 0;
echo "Final active notifications count: {$finalActiveCount}\n";

if ($finalActiveCount >= $activeCount) {
    echo "✅ SUCCESS: Notification restored to active list\n";
} else {
    echo "❌ FAILED: Notification not restored to active list\n";
}

echo "\n=== Soft Delete Test Complete ===\n";
