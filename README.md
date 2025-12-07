# Resume Registry Application

A complete PHP & MySQL CRUD web application for managing user profiles.
Each user can log in and manage only their own profiles.

## Features

- Login system using hashed passwords
- Session-based authentication
- **CRUD operations**:
  - Create new profiles
  - View all profiles in a dynamic table
  - Each user can update only their own profiles
  - Delete profiles
- Client & Server-side form validation
- Flash messages for success/error feedback
- Secure output with htmlentities() to prevent XSS
- Bootstrap UI

## Technologies

- PHP 8+ (with PDO for database interaction)
- MySQL
- PDO (Prepared Statements)
- HTML5 / CSS3
- Bootstrap 5
- Basic JavaScript

## Database Setup

To get started, use the included `NOTES.sql` file. It creates the database, users, and tables with sample data.
