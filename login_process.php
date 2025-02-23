<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $auth = new Auth($conn);
    if ($auth->login($username, $password)) {
        header('Location: index.php');
    } else {
        header('Location: login.php?error=invalid_credentials');
    }
}
?>