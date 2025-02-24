<?php
require_once 'config.php';
require_once 'auth.php';

// Biztosítjuk, hogy a session el van indítva
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $auth = new Auth($conn);
    $user_id = $auth->login($username, $password);
    
    if ($user_id) {
        $_SESSION['is_logged'] = true;
        $_SESSION['user_id'] = $user_id;
        header('Location: index.php');
    } else {
        header('Location: login.php?error=invalid_credentials');
    }
}
?>