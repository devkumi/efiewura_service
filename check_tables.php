<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== BOOKINGS TABLE COLUMNS ===\n";
$bookingsColumns = Schema::getColumnListing('bookings');
foreach ($bookingsColumns as $column) {
    echo "- $column\n";
}

echo "\n=== USERS TABLE COLUMNS ===\n";
$usersColumns = Schema::getColumnListing('users');
foreach ($usersColumns as $column) {
    echo "- $column\n";
}

echo "\n=== PROPERTIES TABLE COLUMNS ===\n";
$propertiesColumns = Schema::getColumnListing('properties');
foreach ($propertiesColumns as $column) {
    echo "- $column\n";
}

// Check if new columns were added
echo "\n=== STATUS CHECK ===\n";
echo "Users has status: " . (in_array('status', $usersColumns) ? 'YES' : 'NO') . "\n";
echo "Properties has status: " . (in_array('status', $propertiesColumns) ? 'YES' : 'NO') . "\n";
echo "Bookings has status: " . (in_array('status', $bookingsColumns) ? 'YES' : 'NO') . "\n";

echo "\n=== DELETED_BY CHECK ===\n";
echo "Users has deleted_by: " . (in_array('deleted_by', $usersColumns) ? 'YES' : 'NO') . "\n";
echo "Properties has deleted_by: " . (in_array('deleted_by', $propertiesColumns) ? 'YES' : 'NO') . "\n";
echo "Bookings has deleted_by: " . (in_array('deleted_by', $bookingsColumns) ? 'YES' : 'NO') . "\n";

echo "\n=== DELETED_AT CHECK ===\n";
echo "Users has deleted_at: " . (in_array('deleted_at', $usersColumns) ? 'YES' : 'NO') . "\n";
echo "Properties has deleted_at: " . (in_array('deleted_at', $propertiesColumns) ? 'YES' : 'NO') . "\n";
echo "Bookings has deleted_at: " . (in_array('deleted_at', $bookingsColumns) ? 'YES' : 'NO') . "\n";
