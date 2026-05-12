# 🏠 Efiewura - Property Rental Management Platform

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

**Efiewura** is a comprehensive property rental management platform that connects landlords and tenants with automated workflows, time-based notifications, and powerful administrative tools.

## ✨ Key Features

### 🏠 **Property Management**
- Property listing and search functionality
- Multi-category property support (Apartment, House, Studio, etc.)
- Image upload and property gallery
- Location-based filtering and search
- Real-time availability status

### 📅 **Booking System**
- Seamless booking request workflow
- Landlord approval system
- Payment tracking and management
- Booking history and status tracking
- Automated confirmation emails

### 🔔 **Smart Notification System**
- **Time-Based Notifications**: Automated lease expiry, payment reminders, move-in/out notifications
- **Auto-Release Feature**: Configurable property auto-release for overdue payments
- **Email Integration**: Beautiful HTML email templates
- **In-App Notifications**: Real-time notification system
- **Duplicate Prevention**: Smart notification deduplication

### ⏰ **Automated Workflows**
- **Lease Management**: 60/30/7-day lease expiry reminders
- **Payment Reminders**: 5-day advance, due date, and overdue notifications
- **Move-In/Out Coordination**: 7-day preparation reminders
- **Auto-Release**: Landlord-configurable overdue property release (7-365 days)

### 👥 **Role-Based Access Control**
- **Admin**: Full platform management access
- **Landlord**: Property and booking management
- **Tenant**: Property browsing and booking
- **User**: Basic platform access

### 🛠️ **Super Admin Portal**
- **Dashboard Analytics**: Real-time system metrics and insights
- **User Management**: Complete user lifecycle management
- **Property Oversight**: Content moderation and property management
- **Booking Administration**: Transaction monitoring and dispute resolution
- **System Monitoring**: Logs, performance metrics, and health checks
- **Advanced Analytics**: 7/30/90-day trend analysis and reporting

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- SQLite or MySQL database
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd efiewura
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

6. **Set up task scheduling** (for time-based notifications)
   ```bash
   # Add to cron (Linux/Mac) or Task Scheduler (Windows)
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

## 📚 Documentation

### API Documentation
- **[Complete API Reference](API_DOCUMENTATION.md)** - Comprehensive endpoint documentation
- **[Super Admin Portal Guide](SUPER_ADMIN_PORTAL_DOCS.md)** - Admin portal detailed documentation

### Key Endpoints
- **Authentication**: `/api/register`, `/api/login`
- **Properties**: `/api/properties` (CRUD operations)
- **Bookings**: `/api/bookings` (Request, confirm, manage)
- **Notifications**: `/api/notifications` (View, mark as read)
- **Admin Portal**: `/api/admin/*` (Complete platform management)
- **Landlord Settings**: `/api/landlord/settings` (Auto-release configuration)

## 🧪 Testing

The platform includes comprehensive test suites located in the `test_scripts/` directory:

### **Quick Testing**
```bash
# Test basic system functionality
php test_scripts/test_simple.php

# Test admin portal functionality  
php test_scripts/test_admin_portal.php

# Test notification system
php test_scripts/test_notifications.php

# Test email functionality
php test_scripts/test_smtp_final.php
```

### **Setup Testing Data**
```bash
# Create admin user
php test_scripts/create_admin.php

# Create sample data
php test_scripts/create_sample_data.php

# Create test landlord
php test_scripts/create_new_landlord.php
```

For complete testing documentation, see [`test_scripts/README.md`](test_scripts/README.md).

# Test API endpoints
php test_api.php

# Run PHPUnit tests
php artisan test
```

## ⚙️ Configuration

### Time-Based Notifications
Configure notification triggers in `config/email_notifications.php`:
- Lease expiry reminders: 60, 30, 7 days
- Payment reminders: 5 days advance, due date, 3 days overdue
- Move-in/out reminders: 7 days advance

### Landlord Settings
Landlords can configure auto-release settings:
- **Overdue Release Days**: 7-365 days (default: 30)
- **API Endpoints**: GET/PATCH `/api/landlord/settings`

### Email Configuration
Update `.env` for email delivery:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

## 📊 System Architecture

### Core Components
- **Laravel 11**: Modern PHP framework
- **Sanctum**: API authentication
- **Eloquent ORM**: Database interactions
- **Task Scheduler**: Automated notifications
- **Blade Templates**: Email rendering
- **SQLite/MySQL**: Data persistence

### Notification Flow
1. **Daily Check**: Automated command runs at 9:00 AM
2. **Date Analysis**: Checks lease dates, payment dues, move-in/out dates
3. **Notification Creation**: Creates appropriate notifications
4. **Email Delivery**: Sends HTML emails via configured mailer
5. **Auto-Release**: Releases overdue properties based on landlord settings

## 🔒 Security Features

- **API Authentication**: Laravel Sanctum token-based authentication
- **Role-Based Access**: Middleware-enforced role permissions
- **Input Validation**: Comprehensive request validation
- **XSS Protection**: Built-in Laravel security features
- **Rate Limiting**: API endpoint rate limiting
- **Admin Access Control**: Super admin portal security

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- **Documentation**: Check the API_DOCUMENTATION.md and SUPER_ADMIN_PORTAL_DOCS.md
- **Issues**: Create an issue on GitHub
- **Email**: api-support@efiewura.com

## 🏆 Features Completed

✅ **Property Management System**  
✅ **User Authentication & Authorization**  
✅ **Booking Workflow**  
✅ **Time-Based Notification System**  
✅ **Auto-Release Functionality**  
✅ **Email Integration with HTML Templates**  
✅ **Super Admin Portal**  
✅ **Comprehensive API Documentation**  
✅ **Test Suites & Validation**  
✅ **Landlord Settings Management**

---

**Built with ❤️ using Laravel**
