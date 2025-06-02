const Header = () => (
  <header className="header">
    <div className="logo-title">
      <img src="img/icon.png" alt="Logo" className="logo" />
      <h1>Book MyBooks</h1>
    </div>
    <nav>
      <ul className="nav-links">
        <li><a href="index.html"><span>Home</span><img src="img/icons8-home-100.png" /></a></li>
        <li><a href="./bookdbapi/booklist.php"><span>Books</span><img src="img/icons8-stack-of-books-94.png" /></a></li>
        <li><a href="./bookdbapi/bookcart.php"><span>Cart</span><img src="img/icons8-cart-64.png" /></a></li>
        <li><a href="./bookdbapi/register.php"><span>Login</span><img src="img/icons8-login-80.png" /></a></li>
      </ul>
    </nav>
  </header>
);
