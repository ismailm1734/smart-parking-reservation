[README_SmartParking.md](https://github.com/user-attachments/files/29516956/README_SmartParking.md)
# Smart Parking Reservation System

> A full-stack parking reservation system with a hybrid SQL + NoSQL data layer.

Built as a project for the Database Systems course, this application lets users
find and reserve parking spots and manage their reservations, with separate
interfaces for regular users and administrators. It combines a relational database
(MySQL) for structured, transactional data with MongoDB for document-style data,
demonstrating a hybrid persistence design.

## Features

- User registration and reservation management
- Parking-spot availability tracking
- Separate **user** and **admin** interfaces
- Stored procedures and triggers for database-side logic
- Hybrid data layer: relational (MySQL) + document store (MongoDB)

## Tech Stack

- **Backend:** PHP
- **Databases:** MySQL (with stored procedures & triggers), MongoDB
- **Environment:** XAMPP (Apache + MySQL)

## Database Design

- Normalized relational schema (see `SQLDump.sql`)
- Stored procedures (`procedure_page.php`) and triggers (`trigger_page.php`)
  for server-side data operations
- MongoDB integration (`mongo.php`) for document-style records
- Connection handled via `db.php`

## Project Structure

```
Scripts/
├── user/     # user-facing pages (index, tickets, reservations)
└── admin/    # admin pages (management, procedures, triggers)
SQLDump.sql   # relational schema + data
```

## Getting Started

**Prerequisites:** XAMPP (Apache + PHP + MySQL), MongoDB.

1. Clone the repository
2. Import `SQLDump.sql` into MySQL (e.g. via phpMyAdmin)
3. Check the database settings in `db.php` (defaults to localhost / root)
4. Start Apache & MySQL via XAMPP, and start MongoDB
5. Open the project in your browser via `http://localhost/`

## Notes

Project for CS 306 Database Systems.
*Tip: add a screenshot or short demo GIF here — recruiters like seeing the project run.*
