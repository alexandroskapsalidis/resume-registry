# Resume Registry Application

A complete PHP & MySQL CRUD web application for managing resume profiles.
Each user can log in and manage only their own profiles, including dynamic work positions.

## Features

- Login system using hashed passwords
- Session-based authentication
- **CRUD operations**:
  - Create new profiles
  - View all profiles in a dynamic table
  - Each user can update only their own profiles
  - Delete profiles with confirmation prompt
- **Work Positions** (One-to-Many Relationship):
  - Each profile can have up to 9 work positions
  - Positions are dynamically added/removed using **jQuery**
- **Education History**:
  - Each profile can have up to **9 education entries**
  - Implemented using a **Many-to-Many relationship**
    between profiles and institutions (via the `Education` table)
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
- JavaScript (Vanilla & jQuery)
- JSON (AJAX data exchange)

## Database Setup

To get started, use the included `NOTES.sql` file. It creates the database, users, and tables with sample data.

## View Project

View the project online using the following credentials

- Email: demo@email.com
- Password: 123
<p>Live Demo 🌐: <a href="https://kapsalidis-resume-registry.great-site.net/" target="_blank">Resume Registry</a></p>
