<?php

$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'webshop_engine'
];

// Database connection
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

?>