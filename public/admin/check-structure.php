<?php
require_once '../../src/bootstrap.php';
header('Content-Type: text/plain');

echo "=== DATABASE STRUCTURE CHECK ===\n\n";

// Check user_profiles table
echo "user_profiles table:\n";
try {
    $columns = $db->fetchAll("DESCRIBE user_profiles");
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\nusers table:\n";
try {
    $columns = $db->fetchAll("DESCRIBE users");
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\nenrollments table:\n";
try {
    $columns = $db->fetchAll("DESCRIBE enrollments");
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\npayments table:\n";
try {
    $columns = $db->fetchAll("DESCRIBE payments");
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\nreviews table:\n";
try {
    $columns = $db->fetchAll("DESCRIBE reviews");
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}
