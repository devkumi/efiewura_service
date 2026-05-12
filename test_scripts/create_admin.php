<?php

// Create admin user
$baseUrl = 'http://127.0.0.1:8000/api';

echo "Creating Admin User...\n";

$adminData = [
    'firstname' => 'Nana',
    'lastname' => 'Yaw',
    'email' => 'jhaykhoma@gmail.com',
    'password' => 'admin123',
    'password_confirmation' => 'admin123',
    'role' => 'admin'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($adminData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decodedResponse = json_decode($response, true);

if ($httpCode == 201) {
    echo "Admin user created successfully!\n";
    echo "Email: jhaykhoma@gmail.com\n";
    echo "Password: admin123\n";
    echo "Token: " . $decodedResponse['data']['token'] . "\n";
} else {
    echo "Failed to create admin user:\n";
    echo $response . "\n";
}
