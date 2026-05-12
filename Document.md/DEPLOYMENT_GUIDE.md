# 🚀 Efiewura Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the Efiewura property rental management platform to production.

## 📋 Pre-Deployment Checklist

### System Requirements
- **PHP**: 8.2 or higher
- **Web Server**: Apache or Nginx
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Memory**: Minimum 512MB RAM (recommended 1GB+)
- **Storage**: Minimum 1GB free space
- **SSL Certificate**: Required for production

### Required PHP Extensions
```bash
php-curl
php-json
php-mbstring
php-openssl
php-pdo
php-tokenizer
php-xml
php-zip
php-gd
php-fileinfo
```

## 🛠️ Deployment Steps

### 1. Server Setup

#### For Ubuntu/Debian:
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and required extensions
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-curl php8.2-json php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd php8.2-fileinfo

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo apt install nginx

# Install MySQL
sudo apt install mysql-server
```

#### For CentOS/RHEL:
```bash
# Install PHP and extensions
sudo yum install php82 php82-fpm php82-mysql php82-curl php82-json php82-mbstring php82-xml php82-zip php82-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo yum install nginx

# Install MySQL
sudo yum install mysql-server
```

### 2. Database Setup

```sql
-- Create database
CREATE DATABASE efiewura_production;

-- Create user
CREATE USER 'efiewura_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON efiewura_production.* TO 'efiewura_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Application Deployment

```bash
# Clone repository
git clone <your-repository-url> /var/www/efiewura
cd /var/www/efiewura

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data /var/www/efiewura
sudo chmod -R 755 /var/www/efiewura
sudo chmod -R 775 /var/www/efiewura/storage
sudo chmod -R 775 /var/www/efiewura/bootstrap/cache
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

#### Required Environment Variables:
```env
APP_NAME=Efiewura
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=efiewura_production
DB_USERNAME=efiewura_user
DB_PASSWORD=secure_password_here

# Email Configuration (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.yourdomain.com

# Queue Configuration (for background jobs)
QUEUE_CONNECTION=database

# Cache Configuration
CACHE_DRIVER=file
```

### 5. Database Migration

```bash
# Run migrations
php artisan migrate --force

# Seed database (optional, for initial data)
php artisan db:seed --force

# Create property categories
php artisan tinker
```

In Tinker, run:
```php
use App\Models\PropertyCategory;

PropertyCategory::create(['name' => 'Apartment', 'description' => 'Residential apartment units']);
PropertyCategory::create(['name' => 'House', 'description' => 'Single-family houses']);
PropertyCategory::create(['name' => 'Studio', 'description' => 'Studio apartments']);
PropertyCategory::create(['name' => 'Commercial', 'description' => 'Commercial properties']);
PropertyCategory::create(['name' => 'Land', 'description' => 'Land plots for development']);

exit
```

### 6. Create Super Admin User

```bash
# Create admin user
php artisan tinker
```

In Tinker:
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::create([
    'name' => 'Super Admin',
    'email' => 'admin@yourdomain.com',
    'password' => Hash::make('secure_admin_password'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);

echo "Admin user created with email: admin@yourdomain.com\n";
exit
```

### 7. Web Server Configuration

#### Nginx Configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    
    root /var/www/efiewura/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    # API rate limiting
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}

# Rate limiting configuration (add to http block)
limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
```

#### Apache Configuration (.htaccess already included):
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/efiewura/public
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/efiewura/public
    
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    
    <Directory /var/www/efiewura/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 8. Task Scheduler Setup

```bash
# Add to crontab
crontab -e

# Add this line:
* * * * * cd /var/www/efiewura && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Queue Worker Setup (Optional but Recommended)

Create systemd service for queue worker:
```bash
sudo nano /etc/systemd/system/efiewura-worker.service
```

Service file content:
```ini
[Unit]
Description=Efiewura Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/efiewura/artisan queue:work --sleep=3 --tries=3 --max-time=3600
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Enable and start the service:
```bash
sudo systemctl enable efiewura-worker
sudo systemctl start efiewura-worker
```

### 10. Log Rotation Setup

```bash
sudo nano /etc/logrotate.d/efiewura
```

Logrotate configuration:
```
/var/www/efiewura/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    copytruncate
}
```

### 11. SSL Certificate Setup (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

## 🔧 Post-Deployment Configuration

### 1. Test API Endpoints

```bash
# Test basic API
curl -X GET https://yourdomain.com/api/properties

# Test admin login
curl -X POST https://yourdomain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@yourdomain.com","password":"secure_admin_password"}'
```

### 2. Configure Email Testing

```bash
# Send test email
php artisan tinker
```

In Tinker:
```php
use App\Models\User;
use App\Services\EmailNotificationService;

$user = User::first();
$emailService = new EmailNotificationService();
$emailService->sendBookingConfirmation($user, 'Test booking confirmation');
exit
```

### 3. Performance Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

## 📊 Monitoring & Maintenance

### 1. Health Check Script

Create a health check endpoint:
```bash
curl https://yourdomain.com/api/admin/system/health
```

### 2. Log Monitoring

```bash
# Monitor application logs
tail -f /var/www/efiewura/storage/logs/laravel.log

# Monitor Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### 3. Database Backup

```bash
# Create backup script
nano /usr/local/bin/efiewura-backup.sh
```

Backup script:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/efiewura"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u efiewura_user -p efiewura_production > $BACKUP_DIR/database_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/efiewura

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Make executable and add to cron:
```bash
chmod +x /usr/local/bin/efiewura-backup.sh

# Add to crontab (daily at 2 AM)
0 2 * * * /usr/local/bin/efiewura-backup.sh
```

## 🔐 Security Checklist

### 1. Server Security
- [ ] Disable root SSH login
- [ ] Use SSH keys instead of passwords
- [ ] Configure firewall (ufw/iptables)
- [ ] Install fail2ban
- [ ] Keep system updated

### 2. Application Security
- [ ] APP_DEBUG=false in production
- [ ] Strong database passwords
- [ ] SSL certificate installed
- [ ] Rate limiting configured
- [ ] File upload validation
- [ ] Input sanitization

### 3. Database Security
- [ ] Database user has minimal required privileges
- [ ] Database server not accessible from internet
- [ ] Regular security updates
- [ ] Encrypted connections

## 🚨 Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
sudo chown -R www-data:www-data /var/www/efiewura
sudo chmod -R 755 /var/www/efiewura
sudo chmod -R 775 /var/www/efiewura/storage
sudo chmod -R 775 /var/www/efiewura/bootstrap/cache
```

#### 2. Database Connection Issues
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

#### 3. Email Not Sending
```bash
# Test email configuration
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

#### 4. Scheduler Not Running
```bash
# Check if cron job is running
php artisan schedule:list

# Run manually to test
php artisan schedule:run
```

## 📞 Support

For deployment support:
- Check logs: `/var/www/efiewura/storage/logs/laravel.log`
- Review configuration: `.env` file
- Test API endpoints with provided test scripts
- Monitor system resources and performance

---

**🎉 Congratulations! Your Efiewura platform is now deployed and ready for production use.**
