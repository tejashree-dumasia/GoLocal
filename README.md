# GoLocal App 🗺️

This repository contains the full codebase for the GoLocal micro-adventure planner, including both the frontend and the backend API.

## ✨ Overview

- **`/frontend`**: A client built with HTML, Bootstrap, and jQuery.
- **`/backend`**: A REST API built with PHP.

## 🚀 Backend Setup

1.  Navigate to the `/backend` directory: `cd backend`
2.  Run `composer install` to download dependencies.
3.  Create a MySQL database named `golocal_db` and import a database schema.
4.  Create `backend/config/database.php` from the template and add your credentials.
5.  Create `backend/config/core.php` from the template and add your JWT secret key.
6.  Point your local server (e.g., XAMPP) document root to the `backend/public` directory.

## 💻 Frontend Setup

1.  Ensure the backend API is running.
2.  Update the `apiUrl` variables in each `.html` file inside the `/frontend` directory to point to your backend's public URL.
3.  Open the `.html` files directly in your browser or serve them from a simple web server.