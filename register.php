<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, address, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $address, $phone]);
        
        // Auto-login after registration
        $user = $pdo->lastInsertId();
        $_SESSION['user_id'] = $user;
        $_SESSION['username'] = $username;
        $_SESSION['address'] = $address;
        $_SESSION['phone'] = $phone;
        
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error_message = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        .registration-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="password"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #4b6cb7;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="registration-form">
        <h2>Register New Account</h2>
        <?php if (isset($error_message)): ?>
            <div style="color: red; margin-bottom: 15px;"><?= $error_message ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" required></textarea>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p style="margin-top: 15px; text-align: center;">
            Already have an account? <a href="./login.php">Login here</a>
        </p>
    </div>
</body>
</html>