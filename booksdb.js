// Define fetch functions
const API_URL = "http://localhost/bookdbapi/booklist.php";

const fetchBooks = async () => {
  const res = await fetch(API_URL);
  return res.json();
};

const postBook = async (book) => {
  const res = await fetch(API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(book),
  });
  return res.json();
};

// Define BookList
const BookList = () => {
  const [books, setBooks] = React.useState([]);
  const [formData, setFormData] = React.useState({
    title: "",
    author: "",
    description: "",
    year: "",
    publisher: "",
    type: "Fiction",
  });

  React.useEffect(() => {
    fetchBooks().then(setBooks);
  }, []);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    await postBook(formData);
    const updatedBooks = await fetchBooks();
    setBooks(updatedBooks);
    setFormData({
      title: "",
      author: "",
      description: "",
      year: "",
      publisher: "",
      type: "Fiction",
    });
  };

  return (
    <div className="container mt-4">
      <h2>Add Book</h2>
      <form onSubmit={handleSubmit}>
        <input className="form-control mb-2" name="title" placeholder="Title" value={formData.title} onChange={handleChange} required />
        <input className="form-control mb-2" name="author" placeholder="Author" value={formData.author} onChange={handleChange} required />
        <textarea className="form-control mb-2" name="description" placeholder="Description" value={formData.description} onChange={handleChange} required />
        <input className="form-control mb-2" name="year" placeholder="Year" type="number" value={formData.year} onChange={handleChange} />
        <input className="form-control mb-2" name="publisher" placeholder="Publisher" value={formData.publisher} onChange={handleChange} />
        <select className="form-control mb-2" name="type" value={formData.type} onChange={handleChange}>
          <option>Fiction</option>
          <option>Non-Fiction</option>
          <option>Romance</option>
          <option>Sci-Fi</option>
        </select>
        <button className="btn btn-primary">Submit</button>
      </form>

      <h3 className="mt-4">Book List</h3>
      <ul className="list-group">
        {books.map((book) => (
          <li className="list-group-item" key={book.id}>
            <strong>{book.title}</strong> by {book.author} <br />
            <small>{book.description}</small> <br />
            <em>{book.year}, {book.publisher} ({book.type})</em>
          </li>
        ))}
      </ul>
    </div>
  );
};
