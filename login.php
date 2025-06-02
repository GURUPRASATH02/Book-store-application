<?php
session_start();
require 'config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['phone'] = $user['phone'];
            header("Location: index.php");
            exit;
        } else {
            $error_message = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $error_message = "Login failed: " . $e->getMessage();
    }
}
?>