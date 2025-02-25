<?php

$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'webshop_engine'
];

// Database connection
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

// Monogram generálása a teljes névből
function generateMonogram($fullName) {
    if (empty($fullName)) return "?";
    
    $parts = explode(" ", $fullName);
    if (count($parts) >= 2) {
        return mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr($parts[1], 0, 1, 'UTF-8');
    } else {
        return mb_substr($fullName, 0, 2, 'UTF-8');
    }
}


?>