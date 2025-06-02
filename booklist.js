const BookMyBooks = () => {
  return (
    <div className="main-container">
      <Header />
      {/* <BookList />  ✅ Now BookList is defined earlier */}
      <Footer />
    </div>
  );
};

const root = ReactDOM.createRoot(document.getElementById("react-container"));
root.render(<BookMyBooks />);
