<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'book_store';
$charset = 'utf8mb4';

try {
    // First try to connect directly
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If database doesn't exist, create it
    if ($e->getCode() === 1049) {
        try {
            $conn = new PDO("mysql:host=$host", $user, $pass);
            $conn->exec("CREATE DATABASE `$dbname`");
            
            // Reconnect to new database
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Create tables
            $pdo->exec("
                CREATE TABLE books (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    author VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    year INT,
                    publisher VARCHAR(255),
                    type ENUM('Fiction', 'Non-Fiction', 'Romance', 'Sci-Fi', 'Mystery', 'Thriller', 'Biography', 'History', 'Fantasy', 'Science', 'Children') NOT NULL,
                    price FLOAT NOT NULL,
                    stock INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                
                CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    address TEXT NOT NULL,
                    phone VARCHAR(20) NOT NULL
                );
                
                CREATE TABLE cart (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    book_id INT NOT NULL,
                    quantity INT NOT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    FOREIGN KEY (book_id) REFERENCES books(id)
                );
                
                CREATE TABLE orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    book_id INT NOT NULL,
                    quantity INT NOT NULL,
                    total_price FLOAT NOT NULL,
                    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    FOREIGN KEY (book_id) REFERENCES books(id)
                );
            ");
        } catch (PDOException $ex) {
            die("Database creation failed: " . $ex->getMessage());
        }
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}
?>