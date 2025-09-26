# Things to remember about this project

## Setting up the virtual host

1. Add this at the end of `c:\xampp\apache\conf\extra\httpd-vhosts.conf`

    ```xml
    <VirtualHost *:80>
    DocumentRoot "path\to\GoLocal\golocal-backend"
    ServerName devlog.local
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

    `127.0.0.1 golocal`

## which file, whai do

### [README.md](/README.md)

### [note.md](/note.md)

### [LICENSE](/LICENSE)

### [db.sql](/db.sql)

### [.gitignore](/.gitignore)

### [frontend/index.html](/golocal_frontend/index.html)

### [frontend/login-test.html](/golocal_frontend/login-test.html)

### [frontend/profile.html](/golocal_frontend/profile.html)

### [frontend/create-trip.html](/golocal_frontend/create-trip.html)

### [frontend/my-trip.html](/golocal_frontend/my-trip.html)

### [frontend/trip-detail.html](/golocal_frontend/trip-detail.html)

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

### [backend/api/trips/update_participants.php](/golocal_backend/api/trips/update_participant.php)

### [backend/api/trips/read.php](/golocal_backend/api/trips/read.php)

### [backend/api/trips/read_single.php](/golocal_backend/api/trips/read_single.php)

### [backend/api/trips/read_participants.php](/golocal_backend/api/trips/read_participants.php)

### [backend/api/trips/invite.php](/golocal_backend/api/trips/invite.php)

### [backend/api/trips/delete.php](/golocal_backend/api/trips/delete.php)

### [backend/api/trips/delete_participants.php](/golocal_backend/api/trips/delete_participant.php)

### [backend/api/trips/create.php](/golocal_backend/api/trips/create.php)

### [backend/api/photos/](/golocal_backend/api/photos/)

### [backend/api/photos/upload.php](/golocal_backend/api/photos/upload.php)
