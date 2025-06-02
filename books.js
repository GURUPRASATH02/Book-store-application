const BookMyBooks = () => {
  return (
    <div className="main-container">
      <Header />

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

      <section className="category-section">
        <h2>Categories</h2>
        <div className="category-grid">
          {["Fiction", "Non-Fiction", "Romance", "Sci-Fi"].map((cat, index) => (
            <div key={index} className="category-card">{cat}</div>
          ))}
        </div>
      </section>

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

      <Footer />
    </div>
  );
};

const root = ReactDOM.createRoot(document.getElementById('react-container'));
root.render(<BookMyBooks />);
