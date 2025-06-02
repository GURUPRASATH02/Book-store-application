<?php
// Database connection configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "book_store";

// Create database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $description = $conn->real_escape_string($_POST['description']);
    $year = (int)$_POST['year'];
    $publisher = $conn->real_escape_string($_POST['publisher']);
    $type = $conn->real_escape_string($_POST['type']);
    
    $sql = "INSERT INTO books (title, author, description, year, publisher, type) 
            VALUES ('$title', '$author', '$description', $year, '$publisher', '$type')";
    
    if ($conn->query($sql)) {
        $success_message = "Book added successfully!";
    } else {
        $error_message = "Error adding book: " . $conn->error;
    }
}

// Fetch books from database
$books = [];
$sql = "SELECT * FROM books ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Library Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --dark: #212529;
            --light: #f8f9fa;
            --danger: #e63946;
            --warning: #ffaa00;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f4 100%);
            color: var(--dark);
            min-height: 100vh;
            padding-bottom: 40px;
        }
        
        header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .logo i {
            color: var(--success);
        }
        
        .search-container {
            display: flex;
            gap: 10px;
            width: 400px;
        }
        
        .search-container input {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .search-container button {
            background: var(--success);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .search-container button:hover {
            background: #3db8d8;
            transform: translateY(-2px);
        }
        
        h1 {
            text-align: center;
            margin: 2rem 0;
            font-size: 2.5rem;
            color: var(--secondary);
            text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
        }
        
        .form-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .form-container h2 {
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn-submit {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }
        
        .books-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .book-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            height: 180px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(45deg, #6c63ff, #3f51b5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-header .book-icon {
            font-size: 5rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .book-type {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
            flex-grow: 1;
        }
        
        .card-body h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .card-body .author {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
        }
        
        .card-body p {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        .card-footer {
            display: flex;
            justify-content: space-between;
            padding: 0 20px 20px;
            color: #777;
            font-size: 0.9rem;
        }
        
        .publisher {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .year {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #777;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #999;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: #4CAF50;
        }
        
        .notification.error {
            background: #f44336;
        }
        
        @media (max-width: 900px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .form-container {
                position: static;
            }
        }
        
        @media (max-width: 600px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }
            
            .search-container {
                width: 100%;
            }
            
            .books-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>BookVault</span>
                </div>
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search books by title, author or publisher...">
                    <button id="searchBtn"><i class="fas fa-search"></i> Search</button>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <h1>Buy Books</h1>
        
        <div class="main-content">
            <div class="form-container">
                <h2>Your Sugguesting books can added</h2>
                <form id="bookForm" method="POST">
                    <?php if (isset($success_message)): ?>
                        <div class="notification success show"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="notification error show"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Publication Year</label>
                        <input type="number" id="year" name="year" min="1800" max="<?php echo date('Y'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="publisher">Publisher</label>
                        <input type="text" id="publisher" name="publisher">
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Book Type</label>
                        <select id="type" name="type">
                            <option value="">Select type</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Science">Science</option>
                            <option value="Biography">Biography</option>
                            <option value="History">History</option>
                            <option value="Fantasy">Fantasy</option>
                            <option value="Mystery">Mystery</option>
                            <option value="Sci-fi">Sci-fi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="text" id="price" name="price">
                    </div>
                    <div class="form-group">
                        <label for="stock">stock</label>
                        <input type="text" id="stock" name="stock">
                    </div>
                    
                    <button type="submit" class="btn-submit">Add Book to Library</button>
                </form>
            </div>
            
            <div class="books-section">
                <div class="books-container" id="booksContainer">
                    <?php if (count($books) > 0): ?>
                        <?php foreach ($books as $book): ?>
                            <div class="book-card">
                                <div class="card-header">
                                    <i class="fas fa-book book-icon"></i>
                                    <span class="book-type"><?php echo htmlspecialchars($book['type'] ?? 'General'); ?></span>
                                </div>
                                <div class="card-body">
                                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <span class="author">by <?php echo htmlspecialchars($book['author']); ?></span>
                                    <p><?php echo htmlspecialchars($book['description'] ?: 'No description available.'); ?></p>
                                </div>
                                <div class="card-footer">
                                    <div class="publisher">
                                        <i class="fas fa-building"></i>
                                        <span><?php echo htmlspecialchars($book['publisher'] ?: 'Unknown'); ?></span>
                                    </div>
                                    <div class="year">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo htmlspecialchars($book['year'] ?: 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-book"></i>
                            <h3>No Books Found</h3>
                            <p>Add your first book to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const bookForm = document.getElementById('bookForm');
        const booksContainer = document.getElementById('booksContainer');
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        
        // Handle search functionality
        function filterBooks() {
            const searchTerm = searchInput.value.toLowerCase();
            const bookCards = booksContainer.querySelectorAll('.book-card');
            
            bookCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const author = card.querySelector('.author').textContent.toLowerCase();
                const publisher = card.querySelector('.publisher span').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || author.includes(searchTerm) || publisher.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Event Listeners
        searchBtn.addEventListener('click', filterBooks);
        searchInput.addEventListener('keyup', filterBooks);
        
        // Auto-hide notifications after 5 seconds
        const notifications = document.querySelectorAll('.notification.show');
        notifications.forEach(notification => {
            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
        });
    </script>
</body>
</html>