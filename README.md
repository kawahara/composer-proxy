Composer Proxy
==============

composer-proxy can cache package information for composer repository.
It's an effective way to use composer when central repository
(packagist.org) server is too far.

There is an available server in Japan.
(http://composer-proxy.jp/)

System Requirement
------------------

- PHP5.3+

How to install
--------------

Clone it.

    $ git clone https://github.com/kawahara/composer-proxy

Resolve dependencies with composer (See https://getcomposer.org/)

    $ cd /path/to/app
    $ composer install

Change permission for cache directories

    $ cd /path/to/app
    $ chmod 777 cache
    $ chmod 777 web/proxy

Copy configuration file and modify for your needs

    cp config.ini.dist config.ini

Example: Configure web server (Apache)

    <IfModule mod_rewrite.c>
      Options -MultiViews

      RewriteEngine On
      RewriteBase /path/to/app/web
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteRule ^ index.php [QSA,L]
    </IfModule>

Example: Configure web server (Nginx)

    server {
          listen 80;
          listen [::]:80 default_server ipv6only=on;

          root /path/to/app/web;
          server_name localhost;

          location / {
                  index index.php index.html index.htm;
                  try_files $uri @rewriteapp;
          }

          location @rewriteapp {
                  rewrite ^(.*)$ /index.php/$1 last;
          }

          location ~ \.php(/|$) {
                  fastcgi_pass unix:/var/run/php5-fpm.sock;
                  fastcgi_split_path_info ^(.+\.php)(/.*)$;
                  include fastcgi_params;
                  fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
                  fastcgi_param  HTTPS off;
          }
    }

Register the script to crontab

    php /path/to/app/console.php cache:clear-old-file

`cache:clear-old-file` command can delete old cache file. `packages.json` (the root file to define packages) is deleted every 5 minutes (default) by this command. Other file TTL is 6 months.

- --ttl (-t) : TTL of `packages.json`. default is 300 seconds.
- --dry-run : Show the action without real remove operations.
- --without-hashed-file : You can ignore to delete package definition file.
- --hashed-file-ttl : TTL of package definition file. default is 15,552,000 seconds. (6 months)


You can change TTL by options of this command.

If you need to delete all of the cache information, you can delete by following command.

    php /path/to/app/console.php cache:clear-all

