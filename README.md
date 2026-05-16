Student and Library Management System with Node.js API

An integrated school-based information system composed of a PHP Student Management System, a C# Library Management System, and a Node.js REST API connected through a centralized MySQL database. The project enables seamless communication and data sharing between web and desktop platforms using RESTful API architecture.

📌 System Overview

This project follows a centralized database architecture where both systems communicate through a shared Node.js API server connected to MySQL.

Integrated Systems
Student Management System (PHP Web Application)
Library Management System (C# Windows Forms Application)
Node.js REST API Middleware
MySQL Centralized Database

The integration ensures:

centralized data management
seamless communication
consistent student and library records
REST API-based transactions
🛠 Technology Stack
Component	Technology
Student Management System	PHP 7+, HTML5, CSS3, JavaScript
Library Management System	C# Windows Forms (.NET Framework 4.7.2)
API Middleware	Node.js, Express.js
Database	MySQL
API Communication	REST API (JSON)

API Base URL:

http://localhost:3000

📂 Project Structure
student-library-management-system/
│
├── Student-Management-System/
│
├── Library-Management-System/
│
├── student-api-node/
│
├── Database/
│
├── Documentation/
│
└── README.md
🎯 Features
Student Management System
User Login Authentication
Add Student Records
Update Student Information
Parent Management
Classroom Management
Student Pagination
Dashboard Statistics
Library Management System
User Login
Add Books
Issue Books
Return Books
Reserve Books
Reports Generation
Student Integration via API
Node.js REST API
Shared Middleware Between Systems
RESTful API Endpoints
JSON Request/Response Handling
MySQL Database Connectivity
Centralized Data Access

🗄 Database

Database Name:

school_api_db

Main Tables:

books
issuedbooks
parent
reserved_books
student
user

The database is shared between the Student and Library systems to ensure data consistency and integration.

🔗 API Endpoints
Authentication
POST /login
Student Endpoints
GET /students
GET /students/:id
POST /students
POST /students/update
GET /students-page
GET /students-count
Parent Endpoints
GET /parents
GET /parents/:id
POST /parents
POST /parents/update
Library Endpoints
GET /books
POST /books
POST /issue
POST /return-book
POST /reserve
GET /reports

🚀 Installation Guide
1. Clone Repository
git clone https://github.com/MonalizaAlbuen/student-library-management-system.git
2. Import Database
Open XAMPP
Start Apache and MySQL
Open phpMyAdmin
Create database:
school_api_db
Import SQL file from the Database folder
3. Run Node.js API

Open terminal inside:

student-api-node

Install dependencies:

npm install

Run server:

node server.js

API will run on:

http://localhost:3000
4. Run Student Management System

Place the folder inside:

xampp/htdocs/

Open browser:

http://localhost/Student-Management-System
5. Run Library Management System
Open Visual Studio 2022
Open the C# project
Restore NuGet packages
Run the application
📦 Dependencies
Node.js API
express
mysql2
cors
body-parser
C# Application
Newtonsoft.Json
System.Net.Http

👨‍💻 Developed By

Albuen, Monaliza F.

Submitted to:
Emannuel T. Saligue

Southern Leyte State University – San Juan Campus
