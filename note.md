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

## other
