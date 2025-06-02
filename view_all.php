<?php
session_start();
require 'config.php';

// Get all books
$stmt = $pdo->query("SELECT * FROM books");
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add the same styles from your main page */
        .books-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .book-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }
        /* ... include all other styles from your main page ... */
    </style>
</head>
<body>
    <header>
        <!-- Same header as index.php -->
    </header>
    
    <div class="container">
        <h1>All Books</h1>
        <a href="index.php"><button>Back to Home</button></a>
        
        <div class="books-container">
            <?php foreach ($books as $book): ?>
                <!-- Same book card structure as index.php -->
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>