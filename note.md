# Things to remember about this project

## Setting up the virtual host

1. Add this at the end of `c:\xampp\apache\conf\extra\httpd-vhosts.conf`

    ```xml
    <VirtualHost *:80>
        DocumentRoot "path\to\GoLocal\golocal-backend"
        ServerName api.golocal.local
        <Directory "path\to\GoLocal\golocal-backend">
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```

2. Change a line in `httpd.conf`

    - before -> `#Include conf/extra/httpd-vhosts.conf`
    - after  -> `Include conf/extra/httpd-vhosts.conf`

3. Add entry to systems hosts file `C:\Windows\System32\drivers\etc\hosts`

    `127.0.0.1 api.golocal.local`

## which file, what do

### [README.md](/README.md)

Description of the project

### [note.md](/note.md)

Things to remember about this project

### [LICENSE](/LICENSE)

MIT license

### [db.sql](/db.sql)

Project schema definition

### [.gitignore](/.gitignore)

Don't commit these on git

### [frontend/index.html](/golocal_frontend/index.html)

- Registration page
- Works with endpoint `http://golocal/public/api/users/register`
  - request with username, name, password
  - responsed to message

### [frontend/login-test.html](/golocal_frontend/login-test.html)

- Login Page
- Works with endpoint `http://golocal/public/api/users/login`
  - request with email, password
  - respond with jwt token which is saved in localstorage

### [frontend/profile.html](/golocal_frontend/profile.html)

- Profile page of user
- Works with endpoint `http://golocal/public/api/users/read_single`
  - request with jwt from the localStorage
  - response with user details

### [frontend/create-trip.html](/golocal_frontend/create-trip.html)

-

### [frontend/my-trip.html](/golocal_frontend/my-trip.html)


### [frontend/trip-detail.html](/golocal_frontend/trip-detail.html)

Displays the details of a specific trip, including trip name, location, description, dates, estimated cost, and a list of participants. Shows which user is admin or co-admin, and allows the admin to:

- Invite registered users to the trip by email
- Remove participants (except themselves)
- Assign a single co-admin (and change co-admin)

Only users who are participants (admin, co-admin, or invited/accepted) can view this page's data. The page uses JWT authentication and enforces privacy via backend checks. All participant names are shown (not just user IDs), and admin/co-admin are visually indicated.

#### APIs used by trip-detail.html

- **GET /api/trips/read_single?id=TRIP_ID**
  - Request: JWT in Authorization header, trip_id as query param
  - Response: Trip details (trip_id, trip_name, location, description, estimated_cost, start_datetime, end_datetime, admin_id, co_admin_id, admin_name, is_admin)
  - Privacy: Only accessible to participants (admin, co-admin, or invited/accepted users)

- **GET /api/trips/read_participants?id=TRIP_ID**
  - Request: JWT in Authorization header, trip_id as query param
  - Response: List of participants (participant_id, user_id, user_name, user_email, status)
  - Only registered users are included

- **POST /api/trips/invite**
  - Request: JSON body with jwt, trip_id, email
  - Response: Success or error message
  - Both admin and co-admin can invite, and only registered users can be invited

- **POST /api/trips/delete_participant**
  - Request: JSON body with jwt, participant_id
  - Response: Success or error message
  - Only admin can remove participants (not themselves)

- **POST /api/trips/set_coadmin**
  - Request: JSON body with jwt, trip_id, user_id
  - Response: Success or error message
  - Only admin can set or change the co-admin

### [backend/composer.lock](/golocal_backend/composer.lock)

### [backend/composer.json](/golocal_backend/composer.json)

### [backend/vendor/](/golocal_backend/vendor/)

### [backend/public/](/golocal_backend/public/)

### [backend/public/index.php](/golocal_backend/public/index.php)

### [backend/pulic/.htaccess](/golocal_backend/public/.htaccess)

### [backend/core/](/golocal_backend/core/)

### [backend/core/photo.php](/golocal_backend/core/photo.php)

### [backend/core/trip.php](/golocal_backend/core/trip.php)

### [backend/core/user.php](/golocal_backend/core/user.php)

### [backend/config/](/golocal_backend/config/)

### [backend/config/database.php](/golocal_backend/config/database.php)

### [backend/config/core.php](/golocal_backend/config/core.php)

### [backend/api/](/golocal_backend/api/)

### [backend/api/users/](/golocal_backend/api/users/)

### [backend/api/users/register.php](/golocal_backend/api/users/register.php)

### [backend/api/user/read_single.php](/golocal_backend/api/users/read_single.php)

### [backend/api/user/login.php](/golocal_backend/api/users/login.php)

### [backend/api/trips/](/golocal_backend/api/trips/)

### [backend/api/trip/update.php](/golocal_backend/api/trips/update.php)

### [backend/api/trips/read.php](/golocal_backend/api/trips/read.php)

### [backend/api/trips/read_single.php](/golocal_backend/api/trips/read_single.php)

### [backend/api/trips/read_participants.php](/golocal_backend/api/trips/read_participants.php)

### [backend/api/trips/invite.php](/golocal_backend/api/trips/invite.php)

### [backend/api/trips/delete.php](/golocal_backend/api/trips/delete.php)

### [backend/api/trips/delete_participants.php](/golocal_backend/api/trips/delete_participant.php)

### [backend/api/trips/create.php](/golocal_backend/api/trips/create.php)

### [backend/api/photos/](/golocal_backend/api/photos/)

### [backend/api/photos/upload.php](/golocal_backend/api/photos/upload.php)
