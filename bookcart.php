<?php
session_start();
require 'config.php'; // Database connection

// Handle login
$error_message = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['address'] = $user['address'];
        $_SESSION['phone'] = $user['phone'];
    } else {
        $error_message = "Invalid credentials";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle adding to cart
if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $book_id = $_POST['book_id'];
    $quantity = $_POST['quantity'];
    
    // Check stock
    $stmt = $pdo->prepare("SELECT stock FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if ($book && $quantity <= $book['stock']) {
        // Add to cart
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, book_id, quantity) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        ");
        $stmt->execute([$_SESSION['user_id'], $book_id, $quantity]);
        $success_message = "Book added to cart!";
    } else {
        $error_message = "Not enough stock available";
    }
}

// Get cart items for logged in user
$cart_items = [];
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT books.id, books.title, books.author, books.price, cart.quantity 
        FROM cart 
        JOIN books ON cart.book_id = books.id 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    $cart_count = count($cart_items);
}

// Handle checkout
if (isset($_POST['checkout']) && isset($_SESSION['user_id'])) {
    try {
        $pdo->beginTransaction();
        $total_amount = 0;
        
        // Process each cart item
        foreach ($cart_items as $item) {
            // Update stock
            $stmt = $pdo->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
            
            // Create order
            $total_price = $item['price'] * $item['quantity'];
            $total_amount += $total_price;
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, book_id, quantity, total_price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $item['id'], $item['quantity'], $total_price]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        $success_message = "Order placed successfully! Total: $" . number_format($total_amount, 2);
        $cart_items = []; // Clear cart after successful checkout
        $cart_count = 0;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Checkout failed: " . $e->getMessage();
    }
}

// Get books for display
$books = [];
try {
    $stmt = $pdo->query("SELECT * FROM books WHERE stock > 0 ORDER BY created_at DESC");
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching books: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookworm Haven - Book Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            color: white;
            padding: 20px 0;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 2rem;
        }

        .logo h1 {
            font-weight: 600;
            font-size: 1.8rem;
        }

        .user-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #f44336;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid white;
            color: white;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }

        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .notification.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .book-filters {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .books-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .book-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .book-image {
            height: 200px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .card-header {
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .book-type {
            background: #e0e7ff;
            color: #4f46e5;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .card-body {
            padding: 0 15px 15px;
            flex-grow: 1;
        }

        .card-body h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .author {
            display: block;
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        .description {
    color: #555;
    font-size: 0.95rem;
    margin-bottom: 15px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis; /* Add ellipsis for truncated text */
    width: 100%; /* Ensure consistent container width */
}

        .book-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .price {
            font-weight: 600;
            color: #27ae60;
            font-size: 1.2rem;
        }

        .stock {
            font-weight: 500;
            color: #3498db;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-footer {
            background: #f9f9f9;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .publisher, .year {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 0 0 8px 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, #3a5ba0, #101f3d);
        }

        .add-to-cart-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        .cart-section {
            position: fixed;
            top: 0;
            right: -400px;
            width: 380px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .cart-section.open {
            right: 0;
        }

        .cart-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .cart-title {
            font-size: 1.5rem;
            color: #2c3e50;
        }

        .close-cart {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #777;
        }

        .cart-items {
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 60px;
            height: 80px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
        }

        .cart-item-details {
            flex-grow: 1;
        }

        .cart-item-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .cart-item-author {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .cart-item-price {
            font-weight: 600;
            color: #2c3e50;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            margin-top: 8px;
        }

        .quantity-btn {
            width: 28px;
            height: 28px;
            background: #f0f2f5;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-input {
            width: 40px;
            height: 28px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 5px;
        }

        .cart-footer {
            padding: 20px;
            border-top: 2px solid #eee;
        }

        .cart-summary {
            margin-bottom: 20px;
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .checkout-btn {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, #43A047, #1B5E20);
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #f44336;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }

        .page-btn {
            padding: 8px 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .page-btn.active {
            background: #4b6cb7;
            color: white;
            border-color: #4b6cb7;
        }

        .page-btn:hover:not(.active) {
            background: #f5f7fa;
        }

        .login-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 25px;
            max-width: 400px;
            width: 90%;
            z-index: 1001;
        }

        .login-modal h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s;
        }

        .form-group input:focus {
            border-color: #4b6cb7;
            outline: none;
            box-shadow: 0 0 0 2px rgba(75, 108, 183, 0.2);
        }

        .btn-submit {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(75, 108, 183, 0.3);
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .books-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .cart-section {
                width: 100%;
                right: -100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-book"></i>
                <h1>Bookworm Haven</h1>
            </div>
            <div class="user-actions">
                <div class="cart-icon" id="cartIcon">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="?logout" class="btn btn-outline">Logout</a>
                <?php else: ?>
                    <button class="btn btn-outline" onclick="openLoginModal()">Login</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="overlay" id="overlay"></div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="notification success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="notification error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="book-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">Search Books</label>
                    <input type="text" id="search" placeholder="Title, author, or keyword">
                </div>
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category">
                        <option value="">All Categories</option>
                        <option value="Fiction">Fiction</option>
                        <option value="Non-Fiction">Non-Fiction</option>
                        <option value="Science">Science</option>
                        <option value="Biography">Biography</option>
                        <option value="History">History</option>
                        <option value="Fantasy">Fantasy</option>
                    </select>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="price-range">Price Range</label>
                    <select id="price-range">
                        <option value="">All Prices</option>
                        <option value="0-10">Under $10</option>
                        <option value="10-20">$10 - $20</option>
                        <option value="20-50">$20 - $50</option>
                        <option value="50+">Over $50</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="sort">Sort By</label>
                    <select id="sort">
                        <option value="popular">Popularity</option>
                        <option value="newest">Newest Arrivals</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="books-container">
            <?php foreach ($books as $book): ?>
                <div class="book-card">
                    <div class="book-image" style="background: linear-gradient(135deg, #<?php echo substr(md5($book['title']), 0, 6); ?>, #<?php echo substr(md5($book['author']), 0, 6); ?>);">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="card-header">
                        <div class="book-type"><?php echo htmlspecialchars($book['type']); ?></div>
                        <div class="stock">
                            <i class="fas fa-box"></i> <?php echo $book['stock']; ?> in stock
                        </div>
                    </div>
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <span class="author">by <?php echo htmlspecialchars($book['author']); ?></span>
                        <p class="description"><?php 
                            if (!empty($book['description'])) {
                                echo htmlspecialchars(substr($book['description'], 0, 100) . '...');
                            } else {
                                echo "No description available.";
                            }
                        ?></p>
                        <div class="book-meta">
                            <div class="price">$<?php echo number_format($book['price'], 2); ?></div>
                            <div class="rating">
                                <?php
                                $rating = rand(3, 5);
                                for ($i = 0; $i < 5; $i++) {
                                    if ($i < $rating) {
                                        echo $i == 4 && $rating == 4.5 ? '<i class="fas fa-star-half-alt"></i>' : '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="publisher">
                            <i class="fas fa-building"></i>
                            <span><?php echo !empty($book['publisher']) ? htmlspecialchars($book['publisher']) : 'Unknown'; ?></span>
                        </div>
                        <div class="year">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo !empty($book['year']) ? $book['year'] : 'N/A'; ?></span>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn" <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <!-- Shopping Cart -->
    <div class="cart-section" id="cartSection">
        <div class="cart-header">
            <h2 class="cart-title">Your Cart</h2>
            <button class="close-cart" onclick="closeCart()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cart-items">
            <?php if (empty($cart_items)): ?>
                <p>Your cart is empty</p>
            <?php else: ?>
                <?php 
                $cart_total = 0;
                foreach ($cart_items as $item): 
                    $item_total = $item['price'] * $item['quantity'];
                    $cart_total += $item_total;
                ?>
                    <div class="cart-item">
                        <div class="cart-item-image" style="background: linear-gradient(135deg, #<?php echo substr(md5($item['title']), 0, 6); ?>, #<?php echo substr(md5($item['author']), 0, 6); ?>);">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="cart-item-details">
                            <div class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                            <div class="cart-item-author">by <?php echo htmlspecialchars($item['author']); ?></div>
                            <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <div class="cart-item-quantity">
                                <button class="quantity-btn">-</button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                <button class="quantity-btn">+</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-footer">
            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total:</span>
                    <span>$<?php echo isset($cart_total) ? number_format($cart_total, 2) : '0.00'; ?></span>
                </div>
                <form method="POST">
                    <button type="submit" name="checkout" class="checkout-btn">Proceed to Checkout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <h2>Login to Your Account</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn-submit">Login</button>
            <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="closeLoginModal()">Cancel</button>
        </form>
    </div>

    <script>
        // Cart functionality
        const cartIcon = document.getElementById('cartIcon');
        const cartSection = document.getElementById('cartSection');
        const overlay = document.getElementById('overlay');
        const loginModal = document.getElementById('loginModal');
        
        function openCart() {
            cartSection.classList.add('open');
            overlay.classList.add('show');
        }
        
        function closeCart() {
            cartSection.classList.remove('open');
            overlay.classList.remove('show');
        }
        
        function openLoginModal() {
            loginModal.style.display = 'block';
            overlay.classList.add('show');
        }
        
        function closeLoginModal() {
            loginModal.style.display = 'none';
            overlay.classList.remove('show');
        }
        
        cartIcon.addEventListener('click', openCart);
        
        // Close modals when clicking outside
        overlay.addEventListener('click', function() {
            closeCart();
            closeLoginModal();
        });
        
        // Prevent clicks inside modals from closing
        cartSection.addEventListener('click', e => e.stopPropagation());
        loginModal.addEventListener('click', e => e.stopPropagation());
        
        // Filter books
        document.getElementById('search').addEventListener('input', filterBooks);
        document.getElementById('category').addEventListener('change', filterBooks);
        document.getElementById('price-range').addEventListener('change', filterBooks);
        document.getElementById('sort').addEventListener('change', filterBooks);
        
        function filterBooks() {
            const search = document.getElementById('search').value.toLowerCase();
            const category = document.getElementById('category').value;
            const priceRange = document.getElementById('price-range').value;
            const sort = document.getElementById('sort').value;
            
            // In a real application, this would be done with AJAX to the server
            // For this demo, we'll just simulate filtering
            alert(`Filtering books with:\nSearch: ${search}\nCategory: ${category}\nPrice: ${priceRange}\nSort: ${sort}\n\nThis would be implemented with server-side filtering in a real application.`);
        }
        
        // Quantity buttons in cart
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('.quantity-input');
                let value = parseInt(input.value);
                
                if (this.textContent === '+' || this.textContent.includes('+')) {
                    value++;
                } else {
                    value = Math.max(1, value - 1);
                }
                
                input.value = value;
            });
        });
    </script>
</body>
</html>