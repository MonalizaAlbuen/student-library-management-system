const express = require("express");
const mysql = require("mysql2");
const cors = require("cors");
const crypto = require("crypto");

const app = express();
app.use(cors());
app.use(express.json());

const db = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "school_api_db"
});

db.connect(err => {
    if (err) console.log("DB ERROR:", err.message);
    else console.log("MySQL Connected");
});

const success = (res, data) => res.json({ status: "success", data });
const error = (res, message, code = 400) =>
    res.status(code).json({ status: "error", message });

app.post("/login", (req, res) => {
    const { email, password } = req.body || {};

    if (!email || !password) return error(res, "Email and password required");

    const hashed = crypto.createHash("md5").update(password).digest("hex");

    db.query(
        "SELECT role, email FROM user WHERE email=? AND password=?",
        [email, hashed],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            if (result.length === 0) return error(res, "Invalid credentials", 401);
            return success(res, { message: "Login successful", user: result[0] });
        }
    );
});

/* STUDENTS */
app.get("/students", (req, res) => {
    db.query(
        `SELECT sid AS id, fname, lname, COALESCE(email, 'N/A') AS email
         FROM student
         ORDER BY sid DESC`,
        (err, result) => {
            if (err) return error(res, err.message, 500);

            const students = result.map(s => ({
                id: s.id,
                name: `${s.fname} ${s.lname}`,
                email: s.email || "N/A"
            }));

            return success(res, students);
        }
    );
});

app.get("/students-page", (req, res) => {
    const page = Math.max(1, parseInt(req.query.page) || 1);
    const limit = Math.max(1, parseInt(req.query.limit) || 10);
    const start = (page - 1) * limit;

    db.query("SELECT COUNT(*) AS total FROM student", (err, countResult) => {
        if (err) return error(res, err.message, 500);

        const total = countResult[0].total;

        db.query(
            `SELECT sid AS id, fname, lname, COALESCE(email, 'N/A') AS email
             FROM student
             ORDER BY sid DESC
             LIMIT ?, ?`,
            [start, limit],
            (err, result) => {
                if (err) return error(res, err.message, 500);

                const rows = result.map(s => ({
                    id: s.id,
                    name: `${s.fname} ${s.lname}`,
                    email: s.email || "N/A"
                }));

                return success(res, { rows, total });
            }
        );
    });
});

app.get("/students-count", (req, res) => {
    db.query("SELECT COUNT(*) AS total FROM student", (err, result) => {
        if (err) return error(res, err.message, 500);
        return success(res, result[0]);
    });
});

app.get("/students/:id", (req, res) => {
    const sid = parseInt(req.params.id);

    if (!sid) return error(res, "Invalid student ID");

    db.query(
        `SELECT sid, fname, lname, bday, address, parent, gender, classroom, email
         FROM student
         WHERE sid = ?`,
        [sid],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            if (result.length === 0) return error(res, "Student not found", 404);

            return success(res, result[0]);
        }
    );
});

app.post("/students", (req, res) => {
    const { fname, lname, bday, address, parent, gender, classroom, email } = req.body || {};

    if (!fname || !lname || !email) return error(res, "Missing required fields");

    db.query("SELECT sid FROM student WHERE email = ?", [email], (err, existing) => {
        if (err) return error(res, err.message, 500);
        if (existing.length > 0) return error(res, "Email already registered");

        db.query(
            `INSERT INTO student
             (fname, lname, bday, address, parent, gender, classroom, email)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
            [fname, lname, bday, address, parent ?? 0, gender, classroom, email],
            (err, result) => {
                if (err) return error(res, err.message, 500);
                return success(res, {
                    message: "Student added successfully",
                    id: result.insertId
                });
            }
        );
    });
});

app.post("/students/update", (req, res) => {
    const { sid, fname, lname, bday, address, gender, parent, classroom, email } = req.body || {};

    if (!sid || !fname || !lname || !email) return error(res, "Missing required fields");

    db.query(
        "SELECT sid FROM student WHERE email = ? AND sid != ?",
        [email, sid],
        (err, existing) => {
            if (err) return error(res, err.message, 500);
            if (existing.length > 0) return error(res, "Email already used by another student");

            db.query(
                `UPDATE student
                 SET fname=?, lname=?, bday=?, address=?, gender=?, parent=?, classroom=?, email=?
                 WHERE sid=?`,
                [fname, lname, bday, address, gender, parent ?? 0, classroom, email, sid],
                err => {
                    if (err) return error(res, err.message, 500);
                    return success(res, { message: "Student updated successfully" });
                }
            );
        }
    );
});

app.get("/classrooms", (req, res) => {
    db.query(
        "SELECT DISTINCT classroom FROM student WHERE classroom IS NOT NULL AND classroom != '' ORDER BY classroom",
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, result);
        }
    );
});

/* PARENTS */
app.get("/parents", (req, res) => {
    const page = Math.max(1, parseInt(req.query.page) || 1);
    const limit = Math.max(1, parseInt(req.query.limit) || 10);
    const start = (page - 1) * limit;

    db.query("SELECT COUNT(*) AS total FROM parent", (err, countResult) => {
        if (err) return error(res, err.message, 500);

        const total = countResult[0].total;

        db.query(
            `SELECT pid, fname, lname, nic, gender, address, contact, job, email
             FROM parent
             ORDER BY pid DESC
             LIMIT ?, ?`,
            [start, limit],
            (err, rows) => {
                if (err) return error(res, err.message, 500);
                return success(res, { rows, total });
            }
        );
    });
});

app.get("/parents/:id", (req, res) => {
    const pid = parseInt(req.params.id);

    if (!pid) return error(res, "Invalid parent ID");

    db.query(
        "SELECT pid, fname, lname, nic, gender, address, contact, job, email FROM parent WHERE pid = ?",
        [pid],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            if (result.length === 0) return error(res, "Parent not found", 404);
            return success(res, result[0]);
        }
    );
});

app.post("/parents", (req, res) => {
    const { fname, lname, nic, gender, address, email, contact, occupation } = req.body || {};

    if (!fname || !lname || !email) return error(res, "Missing required fields");

    db.query(
        `INSERT INTO parent
         (fname, lname, address, gender, job, contact, nic, email)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [fname, lname, address, gender, occupation, contact, nic, email],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, {
                message: "Parent added successfully",
                id: result.insertId
            });
        }
    );
});

app.post("/parents/update", (req, res) => {
    const { pid, fname, lname, nic, gender, address, email, contact, occupation } = req.body || {};

    if (!pid || !fname || !lname || !email) return error(res, "Missing required fields");

    db.query(
        `UPDATE parent
         SET fname=?, lname=?, address=?, gender=?, job=?, contact=?, nic=?, email=?
         WHERE pid=?`,
        [fname, lname, address, gender, occupation, contact, nic, email, pid],
        err => {
            if (err) return error(res, err.message, 500);
            return success(res, { message: "Parent updated successfully" });
        }
    );
});

app.get("/parents-count", (req, res) => {
    db.query("SELECT COUNT(*) AS total FROM parent", (err, result) => {
        if (err) return error(res, err.message, 500);
        return success(res, result[0]);
    });
});

/* BOOKS */
app.get("/books", (req, res) => {
    db.query(
        `SELECT BookID AS id, Title AS title, Author AS author, Genre AS genre,
                Quantity AS quantity, ISBN AS isbn, PublishedDate AS publishedDate,
                Description AS description
         FROM books`,
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, result);
        }
    );
});

app.get("/books/:id", (req, res) => {
    const bookId = parseInt(req.params.id);

    if (!bookId) return error(res, "Invalid book ID");

    db.query(
        `SELECT BookID AS id, Title AS title, Author AS author, Genre AS genre,
                Quantity AS quantity, ISBN AS isbn, PublishedDate AS publishedDate,
                Description AS description
         FROM books WHERE BookID = ?`,
        [bookId],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            if (result.length === 0) return error(res, "Book not found", 404);
            return success(res, result[0]);
        }
    );
});

app.post("/books", (req, res) => {
    const { title, author, genre, quantity, isbn, publishedDate, description } = req.body || {};

    if (!title || !isbn) return error(res, "Title and ISBN are required");

    db.query("SELECT BookID FROM books WHERE ISBN = ?", [isbn], (err, existing) => {
        if (err) return error(res, err.message, 500);
        if (existing.length > 0) return error(res, "ISBN already exists");

        db.query(
            `INSERT INTO books
             (Title, Author, Genre, Quantity, ISBN, PublishedDate, Description)
             VALUES (?, ?, ?, ?, ?, ?, ?)`,
            [title, author, genre, quantity ?? 1, isbn, publishedDate, description],
            (err, result) => {
                if (err) return error(res, err.message, 500);
                return success(res, {
                    message: "Book added successfully",
                    id: result.insertId
                });
            }
        );
    });
});

app.post("/books/update", (req, res) => {
    const { bookId, title, author, genre, quantity, isbn, publishedDate, description } = req.body || {};

    if (!bookId || !title || !isbn) return error(res, "Missing required fields");

    db.query(
        "SELECT BookID FROM books WHERE ISBN = ? AND BookID != ?",
        [isbn, bookId],
        (err, existing) => {
            if (err) return error(res, err.message, 500);
            if (existing.length > 0) return error(res, "ISBN already used by another book");

            db.query(
                `UPDATE books
                 SET Title=?, Author=?, Genre=?, Quantity=?, ISBN=?, PublishedDate=?, Description=?
                 WHERE BookID=?`,
                [title, author, genre, quantity ?? 1, isbn, publishedDate, description, bookId],
                err => {
                    if (err) return error(res, err.message, 500);
                    return success(res, { message: "Book updated successfully" });
                }
            );
        }
    );
});

app.delete("/books/:id", (req, res) => {
    const bookId = parseInt(req.params.id);

    if (!bookId) return error(res, "Invalid book ID");

    db.query("DELETE FROM books WHERE BookID = ?", [bookId], err => {
        if (err) return error(res, err.message, 500);
        return success(res, { message: "Book deleted successfully" });
    });
});

app.get("/books-count", (req, res) => {
    db.query("SELECT COUNT(*) AS total FROM books", (err, result) => {
        if (err) return error(res, err.message, 500);
        return success(res, result[0]);
    });
});

/* ISSUE / RETURN / RESERVATION */
app.get("/borrowed-books", (req, res) => {
    db.query(
        `SELECT DISTINCT b.BookID, b.Title
         FROM issuedbooks i
         JOIN books b ON i.BookID = b.BookID
         WHERE i.status = 'issued'`,
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, result);
        }
    );
});

app.get("/students-by-book", (req, res) => {
    const { BookID } = req.query;

    if (!BookID) return error(res, "BookID required");

    db.query(
        `SELECT DISTINCT s.sid, CONCAT(s.fname, ' ', s.lname) AS fullname
         FROM issuedbooks i
         JOIN student s ON i.sid = s.sid
         WHERE i.BookID = ? AND i.status = 'issued'`,
        [BookID],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, result);
        }
    );
});

app.get("/issue-id", (req, res) => {
    const { BookID, sid } = req.query;

    if (!BookID || !sid) return error(res, "BookID and sid required");

    db.query(
        "SELECT IssueID FROM issuedbooks WHERE BookID = ? AND sid = ? AND status = 'issued' LIMIT 1",
        [BookID, sid],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            if (result.length === 0) return error(res, "No active issue found");
            return success(res, result[0]);
        }
    );
});

app.post("/return-book", (req, res) => {
    const { IssueID } = req.body || {};

    if (!IssueID) return error(res, "IssueID required");

    db.query(
        "UPDATE issuedbooks SET status='returned', ReturnDate=NOW() WHERE IssueID=?",
        [IssueID],
        err => {
            if (err) return error(res, err.message, 500);
            return success(res, { message: "Book returned successfully" });
        }
    );
});

app.post("/issue", (req, res) => {
    const { book_id, student_id, issue_date, return_date } = req.body || {};

    if (!book_id || !student_id) return error(res, "Missing fields");

    db.query(
        `INSERT INTO issuedbooks
         (BookID, sid, IssueDate, ReturnDate, status)
         VALUES (?, ?, ?, ?, 'issued')`,
        [book_id, student_id, issue_date, return_date],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, {
                message: "Book issued successfully",
                id: result.insertId
            });
        }
    );
});

app.get("/issued", (req, res) => {
    db.query(
        `SELECT i.IssueID AS id,
                b.Title AS book_title,
                CONCAT(s.fname, ' ', s.lname) AS student_name,
                i.IssueDate AS issue_date,
                i.ReturnDate AS return_date,
                i.status
         FROM issuedbooks i
         LEFT JOIN books b ON i.BookID = b.BookID
         LEFT JOIN student s ON i.sid = s.sid
         ORDER BY i.IssueID DESC`,
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, result);
        }
    );
});

app.post("/reserve", (req, res) => {
    const { book_id, student_id } = req.body || {};

    if (!book_id || !student_id) return error(res, "Missing fields");

    db.query(
        "INSERT INTO reserved_books (BookID, sid, ReserveDate) VALUES (?, ?, NOW())",
        [book_id, student_id],
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, {
                message: "Book reserved successfully",
                id: result.insertId
            });
        }
    );
});

app.get("/reservations", (req, res) => {
    db.query(
        `SELECT r.id,
                b.Title AS book_title,
                CONCAT(s.fname, ' ', s.lname) AS student_name,
                r.ReserveDate AS date_reserved
         FROM reserved_books r
         JOIN books b ON r.BookID = b.BookID
         JOIN student s ON r.sid = s.sid
         ORDER BY r.id DESC`,
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, result);
        }
    );
});

app.get("/reservations-count", (req, res) => {
    db.query("SELECT COUNT(*) AS total FROM reserved_books", (err, result) => {
        if (err) return success(res, { total: 0 });
        return success(res, result[0]);
    });
});

app.get("/reports", (req, res) => {
    db.query(
        `SELECT i.IssueID AS id,
                b.Title AS book_title,
                CONCAT(s.fname, ' ', s.lname) AS student_name,
                i.IssueDate AS issue_date,
                i.ReturnDate AS return_date,
                i.status
         FROM issuedbooks i
         LEFT JOIN books b ON i.BookID = b.BookID
         LEFT JOIN student s ON i.sid = s.sid
         ORDER BY i.IssueID DESC`,
        (err, result) => {
            if (err) return error(res, err.message, 500);
            return success(res, result);
        }
    );
});

app.get("/health", (req, res) => success(res, "API running"));

app.listen(3000, () => {
    console.log("Server running on http://localhost:3000");
});