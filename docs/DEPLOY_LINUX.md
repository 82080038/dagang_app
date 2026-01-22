# Deploy di Server Linux

## Apache VirtualHost
```
<VirtualHost *:80>
    ServerName dagang.example.com
    DocumentRoot /var/www/dagang

    <Directory /var/www/dagang>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/dagang_error.log
    CustomLog ${APACHE_LOG_DIR}/dagang_access.log combined
</VirtualHost>
```

## Nginx Server Block
```
server {
    listen 80;
    server_name dagang.example.com;
    root /var/www/dagang;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(css|js|png|jpg|jpeg|gif|svg|ico)$ {
        expires 7d;
        access_log off;
    }
}
```

## Persiapan PHP
- Install paket: php, php-fpm (untuk Nginx), php-mysql, php-mbstring, php-json, php-curl, php-openssl
- Pastikan `pdo_mysql` aktif
- Set `date.timezone` di php.ini
- Pastikan `session.save_path` dapat ditulis

## Permission Direktori
- Pastikan user web memiliki akses tulis ke:
  - `logs/`
  - `cache/`
  - `uploads/` (jika digunakan)
  - `temp/`
- Contoh:
```
sudo chown -R www-data:www-data /var/www/dagang/logs /var/www/dagang/cache /var/www/dagang/uploads /var/www/dagang/temp
sudo chmod -R 755 /var/www/dagang/logs /var/www/dagang/cache /var/www/dagang/uploads /var/www/dagang/temp
```

## Konfigurasi Aplikasi
- Set BASE_URL dan kredensial database di `app/config/config.php` dan `app/config/database.php`
- Pastikan `APP_NAME`, `APP_VERSION` sesuai kebutuhan

## Health Check
- Health check otomatis berjalan saat startup untuk memvalidasi ekstensi PHP dan permission direktori
- Log hasil tersedia di `logs/app.log` dengan level HEALTH/INFO/ERROR
