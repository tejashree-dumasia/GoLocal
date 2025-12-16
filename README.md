# GoLocal - A Micro-Adventure Planner 🗺️

GoLocal is a full-stack web application designed to help friends and groups plan local micro-adventures. It keeps all your planning—participants, checklists, and photos—in one place, moving the clutter away from traditional messaging apps.

This repository is a **monorepo** containing both the complete backend API and the frontend client.



## ✨ Core Features

* **User Authentication**: Secure user registration and login using **JWT (JSON Web Tokens)**.
* **Profile Management**: Users can view their profile, update their username, and upload a profile picture.
* **Trip Management**: Create new trips, view a dashboard of all your trips, and delete trips you own.
* **Participant System**:
    * Trip admins/co-admins can invite participants.
    * Supports inviting registered users (by email) or guests (by name/email).
    * Admins can remove participants from a trip.
* **Trip Checklists**: A shared, real-time checklist for each trip.
    * Any participant can add items.
    * Any participant can check/uncheck items.
    * The item creator or trip admin can delete items.
* **Photo Sharing**:
    * Participants can upload photos to a shared trip gallery.
    * Includes captions and uploader details.

## 🛠️ Tech Stack

* **Backend (in `/backend`)**:
    * **PHP 8+** (OOP)
    * **MySQL** with PDO
    * **REST API** (custom single-entry-point router)
    * **Composer** for package management
    * **`firebase/php-jwt`** for JSON Web Token authentication

* **Frontend (in `/frontend`)**:
    * **AngularJS (v1.x)**
    * **HTML5**
    * **Bootstrap 4**
    * JavaScript (ES5)

* **Environment**:
    * **XAMPP** (Apache & MySQL)
    * **Virtual Host** (e.g., `http://api.golocal.test`) for the backend API.

## 🚀 Getting Started

Follow these steps to get the project running on your local machine.

### Prerequisites

* [XAMPP](https://www.apachefriends.org/index.html) (or any other local server stack with Apache & MySQL)
* [Composer](https://getcomposer.org/)
* [Git](https://git-scm.com/)

---
### 1. Backend Setup

The backend must be running first, as the frontend depends on it.

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/YOUR_USERNAME/golocal-app.git](https://github.com/YOUR_USERNAME/golocal-app.git)
    cd golocal-app
    ```

2.  **Install PHP Dependencies**
    Navigate to the `backend` folder and run Composer.
    ```bash
    cd backend
    composer install
    ```

3.  **Set Up the Database**
    * Start **Apache** and **MySQL** in your XAMPP control panel.
    * Go to `http://localhost/phpmyadmin` and create a new database named `golocal_db`.
    * Find the SQL script we created (or export your current database) and import it into `golocal_db`.

4.  **Configure Environment**
    In the `backend/config/` folder, you will find two `.template` files.
    * **`core.template.php`**: Copy this file and rename it to `core.php`. Open it and set your own unique, random string for the `$secret_key`.
    * **`database.template.php`**: Copy this file and rename it to `database.php`. Open it and fill in your MySQL database credentials (username and password, which are usually "root" and "" for a default XAMPP install).

5.  **Set Up Virtual Host**
    * You need to point a local domain to your `backend/public` folder.
    * **Windows:** Edit your `C:\Windows\System32\drivers\etc\hosts` file and add:
        ```
        127.0.0.1   api.golocal.test
        ```
    * **XAMPP:** Edit your `C:\xampp\apache\conf\extra\httpd-vhosts.conf` file and add:
        ```apache
        <VirtualHost *:80>
            DocumentRoot "C:/xampp/htdocs/golocal-app/backend/public"
            ServerName api.golocal.test
            <Directory "C:/xampp/htdocs/golocal-app/backend/public">
                DirectoryIndex index.php
                AllowOverride All
                Require all granted
            </Directory>
        </VirtualHost>
        ```
    * **Restart Apache** from the XAMPP control panel. Your backend API is now live at `http://api.golocal.test`.

---
### 2. Frontend Setup

1.  **Update API URLs**
    * Go through all the `.html` files in the `frontend/` folder.
    * In the `<script>` section of each file, find the `apiBase` or `apiUrl` variables.
    * Make sure they all point to your new backend URL (e.g., `http://api.golocal.test/api/...`).

2.  **Run the Frontend**
    * You can serve the `frontend` folder using any simple web server.
    * **The easiest way** is to use the **Live Server** extension in VS Code.
    * Right-click `frontend/login-test.html` and select "Open with Live Server".

You can now register, log in, and use the application!

<details>
<summary><b>API Endpoints</b></summary>

-   `POST /api/users/register`
-   `POST /api/users/login`
-   `GET /api/users/read_single`
-   `POST /api/users/update`
-   `POST /api/users/delete`
-   `POST /api/users/upload_profile`
-
-   `POST /api/trips/create`
-   `GET /api/trips/read`
-   `GET /api/trips/read_single`
-   `POST /api/trips/delete`
-
-   `POST /api/trips/invite`
-   `GET /api/trips/read_participants`
-   `POST /api/trips/delete_participant`
-
-   `GET /api/trips/checklist_read`
-   `POST /api/trips/checklist_create`
-   `POST /api/trips/checklist_update`
-   `POST /api/trips/checklist_delete`
-
-   `POST /api/trips/photo_upload`
-   `GET /api/trips/photos_read`

</details>
