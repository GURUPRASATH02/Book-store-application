
const BookMyBooks = () => {
  return (
    <div className="main-container">
      {/* Header */}
      <header className="header">
        <div className="logo-title">
          <img src="img/icon.png" alt="Logo" className="logo" />
          <h1>Book MyBooks</h1>
        </div>
        <nav>
          <ul className="nav-links">
            <li><a href="#"><span>Home</span><img src="img/icons8-home-100.png"/></a></li>
            <li><a href="#"><span>Books</span><img src="img/icons8-stack-of-books-94.png"/></a></li>
            <li><a href="#"><span>Cart</span><img src="img/icons8-cart-64.png"/></a></li>
            <li><a href="#"><span>Login</span><img src="img/icons8-login-80.png"/></a></li>
          </ul>
        </nav>
      </header>

      {/* Hero Section */}
      <section className="hero-section">
        <div className="text-box">
          <h2>Reading Fancistics</h2>
          <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Suscipit voluptatum
            culpa odit fuga. Molestiae consequuntur dolore delectus, placeat harum
            laudantium!
          </p>
        </div>
        <img src="img/icon.png" alt="Decorative" className="hero-image" />
      </section>

      {/* Categories */}
      <section className="category-section">
        <h2>Categories</h2>
        <div className="category-grid">
          {["Fiction", "Non-Fiction", "Romance", "Sci-Fi","Science","History","Biograghy","Fantasy","Mystery"].map((cat, index) => (
            <div key={index} className="category-card">
              {cat}
            </div>
          ))}
        </div>
      </section>

      {/* Subscribe Section */}
      <section className="subscribe-section">
        <div className="subscribe-content">
          <div>
            <h2>Subscribe Now For Daily Tips</h2>
            <p>Get fresh ideas and book recs straight to your inbox.</p>
          </div>
          <div className="subscribe-form">
            <input type="email" placeholder="Enter Your Email Address" />
            <button>Submit</button>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="footer-section">
        <div className="footer-grid">
          <div>
            <h5><img src="img/icon.png" alt="Logo" className="footer-logo" /> Book Store</h5>
            <p>Bringing stories to life, one page at a time.</p>
          </div>
          <div>
            <h6>Short Brief</h6>
            <ul>
              <li><a href="#">How It Works?</a></li>
              <li><a href="#">How Can I Do It?</a></li>
              <li><a href="#">Events & Meetings</a></li>
              <li><a href="#">Payments</a></li>
            </ul>
          </div>
          <div>
            <h6>Quick Link</h6>
            <ul>
              <li><a href="index.html">Home</a></li>
              <li><a href="#">About</a></li>
              <li><a href="#">Contact</a></li>
              <li><a href="./bookdbapi/bookcart.php">Shop Books</a></li>
            </ul>
          </div>
          <div>
            <h6>Support</h6>
            <ul>
              <li><a href="#">F.A.Q</a></li>
              <li><a href="#">Features</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms & Conditions</a></li>
            </ul>
          </div>
        </div>
        <div className="footer-bottom">© 2025 BookMyBooks. All rights reserved.</div>
      </footer>
    </div>
  );
};

const root = ReactDOM.createRoot(document.getElementById('react-container'));
root.render(<BookMyBooks />);
