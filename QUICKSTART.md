# Beyond Barista Academy LMS - Quick Start Guide

## Getting Started

### 1. Prerequisites
- PHP 8.2+
- MySQL 8.0+
- XAMPP or similar local environment

### 2. Initial Setup

```bash
# The database has already been set up and seeded with sample data
# Test accounts are ready to use

# Start the development server
cd c:\xampp\htdocs\bbacademy
php -S localhost:8000 -t public

# Visit: http://localhost:8000
```

### 3. Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@beyondbarista.rw | Admin@2026 |
| Instructor | instructor@beyondbarista.rw | Instructor@2026 |
| Student | student@beyondbarista.rw | Student@2026 |

### 4. Explore the App

#### As a Student:
1. Register or login with student account
2. Go to "Courses" to browse available courses
3. Click on "Foundation Barista Skills" (free course)
4. Click "Start Learning"
5. Watch the lessons (with embedded YouTube videos)
6. Mark lessons complete by clicking "Mark Complete"
7. Check your progress on the dashboard
8. Take the quiz at the end of each course
9. View your certificates

#### As an Instructor:
1. Login with instructor account
2. Go to "Instructor Dashboard"
3. Manage your courses
4. Create new courses, add modules and lessons
5. Create quizzes and questions

#### As an Admin:
1. Login with admin account
2. Go to "Admin Dashboard"  
3. Manage users and content

### 5. Current Features - All Working ✅

- ✅ User registration and authentication
- ✅ Course browsing with filters
- ✅ Free course enrollment
- ✅ Video lessons (YouTube embedded)
- ✅ Text, PDF, and media lessons
- ✅ Progress tracking
- ✅ Quiz system with automatic grading
- ✅ Student dashboard with statistics
- ✅ Responsive design (mobile-friendly)

### 6. Features Not Yet Complete

The following features need implementation:
- ⏳ Paid course checkout & payment processing
- ⏳ Certificate generation (PDF + QR codes)
- ⏳ Email notifications
- ⏳ Blog system (CRUD)
- ⏳ Admin dashboard (full features)
- ⏳ Instructor dashboard (full features)
- ⏳ Multi-language support

### 7. Architecture Overview

The app uses a custom PHP MVC framework:

```
Controllers (app/Controllers/)
  ↓
Models (app/Models/)
  ↓
Views (resources/views/)
  ↓
Database (MySQL)
```

**Routes:** See `routes/web.php` for all URL mappings  
**Database:** See `database/migrations/schema.sql` for table structure

### 8. Key Files to Know

- `/public/index.php` - Application entry point
- `/app/Core/Router.php` - URL routing
- `/app/Core/Database.php` - Database connection & queries
- `/routes/web.php` - URL routes definition
- `/resources/views/` - All HTML templates
- `/database/migrations/schema.sql` - Database structure

### 9. Common Tasks

#### Add a New Route
Edit `/routes/web.php`:
```php
$router->get('/path', [ControllerName::class, 'methodName']);
```

#### Create a New Page
1. Create controller method in `/app/Controllers/`
2. Create view in `/resources/views/`
3. Add route in `/routes/web.php`

#### Query Database
```php
// In a controller
$data = $this->db()->query("SELECT * FROM courses WHERE id = ?", [1])->fetchAll();
$single = $this->db()->fetchOne("SELECT * FROM users WHERE id = ?", [1]);
$this->db()->insert('table_name', ['column' => 'value']);
```

### 10. Debugging

- Turn on debug mode: Set `APP_DEBUG=true` in `.env`
- Check error logs: Browser error messages when debug is on
- Database errors: Check MySQL error logs

### 11. Next Steps

To complete the MVP:

1. **Implement Payment Processing** (Priority 1)
   - Configure Stripe/Flutterwave API keys in `.env`
   - Test payment flow with free course first
   - Implement payment callbacks

2. **Add Certificate Generation** (Priority 2)
   - Install tcpdf or dompdf via composer
   - Implement QR code generation
   - Create certificate PDF template

3. **Build Admin Features** (Priority 3)
   - Complete admin dashboard
   - User management interface
   - Content moderation tools

4. **Email System** (Priority 4)
   - Configure SMTP settings
   - Create email templates
   - Send notifications

### 12. Project Structure

```
bbacademy/
├── app/                      # Application code
│   ├── Controllers/          # 10 main controllers
│   ├── Models/               # 20 data models
│   ├── Services/             # Business logic services
│   ├── Core/                 # Framework core (Router, DB, etc)
│   ├── Middleware/           # Auth, CSRF, etc
│   └── Helpers/              # Utility functions
├── database/
│   ├── migrations/           # schema.sql with all tables
│   └── seeders/              # DatabaseSeeder.php with test data
├── resources/
│   └── views/                # 35+ HTML templates
├── routes/
│   └── web.php               # URL routing rules
├── config/                   # Configuration files
├── public/
│   ├── index.php             # Entry point
│   └── assets/               # CSS, JS, images
└── .env                      # Environment variables (development)
```

### 13. Development Tips

- The app uses a **custom PHP framework** (not Laravel, Symfony, etc)
- All database queries use **prepared statements** for security
- Views use **plain PHP** (no Blade or other template engine)
- Controllers extend a base **Controller class** with helper methods
- Use `$this->render('view/path', $data)` to render views
- Use `$this->json($data)` for AJAX responses

### 14. Important Notes

- **Database already set up** - No migration needed
- **Session-based auth** - Uses PHP sessions for authentication  
- **CSRF protection** - Enabled on all forms
- **Responsive design** - Works on mobile/tablet/desktop

---

## You're Ready! 🚀

The core learning platform is fully functional. Test it out, then implement the remaining features listed above.

See `PROJECT_COMPLETION_SUMMARY.md` for detailed status of all features.
