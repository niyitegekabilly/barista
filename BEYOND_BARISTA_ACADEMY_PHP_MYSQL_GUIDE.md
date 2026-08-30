# Beyond Barista Academy — Premium LMS
## Modern PHP + MySQL Development & Deployment Guide

**Project:** Beyond Barista Academy – Premium Learning Management System (LMS)  
**Target:** Modern, production-ready hospitality learning platform  
**Development:** PHP + MySQL + HTML5 + CSS3 + JavaScript  
**Local Testing:** XAMPP on Windows  
**Production Hosting:** cPanel / Apache / MySQL  
**Architecture:** Secure server-rendered PHP application with modular JavaScript enhancements  
**Primary Goal:** Build a premium LMS that feels comparable to Coursera, Udemy, Alison and Moodle while remaining practical to develop, test and deploy on conventional shared hosting.

---

# 1. PROJECT OBJECTIVE

Build a complete premium Learning Management System for **Beyond Barista Academy Rwanda** (https://www.beyondbarista.rw/) focused on hospitality education.

The platform must support:

- Free and premium courses
- Student registration and authentication
- Course enrollment
- Structured learning paths
- Video, PDF, text, audio and presentation lessons
- Assignments and quizzes
- Final examinations
- Progress tracking
- Course completion
- Premium PDF certificates
- QR-code certificate verification
- Membership plans
- Online payments
- Instructor management
- Administrator management
- Blog and hospitality learning resources
- Events and workshops
- Hospitality jobs and internships
- Reviews, ratings and discussions
- English, French and Kinyarwanda
- SEO and social sharing
- Secure role-based access
- Modern responsive dashboards

The final product must look and feel like a modern SaaS/education platform, not an old-fashioned PHP website.

---

# 2. IMPORTANT ARCHITECTURE DECISION


Use a conventional PHP architecture that works reliably on XAMPP and cPanel shared hosting.

## Required stack

### Backend

- PHP 8.4 or PHP 8.5
- MySQL 8.x / MariaDB compatible SQL where practical
- PDO for all database access
- Sessions for authentication
- REST-style internal endpoints where useful
- Cron jobs for scheduled background operations

### Frontend

- HTML5
- CSS3
- Vanilla JavaScript / ES6+
- Bootstrap 5 or a lightweight custom design system
- Bootstrap Icons or Font Awesome Free
- Chart.js for dashboards and analytics
- SweetAlert2 for polished notifications
- AOS or small custom CSS animations where appropriate
- Google Fonts: Poppins + Inter

### Server

- Apache
- XAMPP for local development
- cPanel / Apache for production
- MySQL database
- HTTPS
- PHP-FPM where the host provides it

Keep the code portable. The same codebase must work locally in XAMPP and on a normal cPanel account with minimal configuration changes.

---

# 3. PHP VERSION STANDARD

Preferred production target:

**PHP 8.5**

Fallback:

**PHP 8.4**

Do not build around deprecated PHP behavior. Write modern PHP using:

- Strict typing where practical
- Namespaces
- Classes and interfaces
- Typed properties and parameters
- Constructor injection where useful
- Exceptions
- PDO prepared statements
- Password hashing with password_hash()
- password_verify()
- Secure session configuration
- CSRF protection
- Output escaping with htmlspecialchars()
- Consistent validation

Avoid:

- mysql_* functions
- Unsafe SQL string concatenation
- Raw $_POST values inserted into SQL
- Hardcoded production passwords
- Hardcoded API keys
- eval()
- unserialize() for untrusted input
- Sensitive information shown in production errors

---

# 4. DEVELOPMENT ENVIRONMENT — XAMPP

Develop and test locally using XAMPP.

Recommended local environment:

- Windows
- XAMPP
- Apache
- PHP 8.4/8.5 if available in the chosen XAMPP build
- MySQL or MariaDB supplied with XAMPP
- phpMyAdmin
- Git
- VS Code
- Composer

Example local project location:

```text
C:\xampp\htdocs\beyond-barista-lms\
```

Local URL:

```text
http://localhost/beyond-barista-lms/
```

Local database example:

```text
Database: beyond_barista_lms
Host: 127.0.0.1
Port: 3306
User: root
Password: [empty for a typical local XAMPP setup]
```

Do not assume the production database credentials are the same as local credentials.

---

# 5. PRODUCTION ENVIRONMENT — CPANEL

The application must be deployable to a standard cPanel account.

Expected cPanel tools:

- File Manager
- MySQL Databases
- phpMyAdmin
- MultiPHP Manager
- MultiPHP INI Editor
- Cron Jobs
- SSL/TLS
- Error Logs
- Select PHP Version / PHP Extensions where supplied by the host

Use **MultiPHP Manager** to select the application PHP version on the production domain.

Use **MultiPHP INI Editor** for production PHP settings instead of assuming access to the server-wide php.ini.

The application must not require root privileges, SSH-only deployment, Node.js, Docker, or server-level services.

---

# 6. REQUIRED PROJECT STRUCTURE

Use a clean modular structure similar to:

```text
beyond-barista-lms/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   ├── Middleware/
│   ├── Helpers/
│   ├── Validators/
│   └── Core/
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── mail.php
│   ├── payments.php
│   └── services.php
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── schema.sql
│
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── fonts/
│   └── uploads/
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── components/
│   │   ├── public/
│   │   ├── student/
│   │   ├── instructor/
│   │   └── admin/
│   ├── lang/
│   │   ├── en/
│   │   ├── fr/
│   │   └── rw/
│   └── emails/
│
├── routes/
│   ├── web.php
│   ├── admin.php
│   ├── instructor.php
│   └── api.php
│
├── storage/
│   ├── logs/
│   ├── cache/
│   ├── exports/
│   └── certificates/
│
├── vendor/
├── .env.example
├── .gitignore
├── composer.json
├── README.md
└── .htaccess
```

If a full framework is used, the implementation may adapt this structure, but the application must remain deployable to cPanel without requiring Node.js in production.

Recommended approach: build a small MVC-style PHP application or use a lightweight PHP framework only if the hosting environment supports it reliably. Do not introduce a framework merely for fashion.

---

# 7. APPLICATION ARCHITECTURE

Use a simple MVC/service architecture.

## Request flow

```text
Browser
   ↓
Apache
   ↓
public/index.php
   ↓
Router
   ↓
Middleware
   ↓
Controller
   ↓
Service / Repository
   ↓
PDO
   ↓
MySQL
   ↓
View / JSON response
```

Controllers handle HTTP requests.

Services handle business logic.

Repositories or models handle data access.

Views handle presentation.

Helpers handle reusable functionality.

Do not place large SQL queries, authentication logic and HTML inside one PHP file.

---

# 8. BRAND — BEYOND BARISTA ACADEMY

## Institution

Beyond Barista Academy

## Country

Rwanda

## Core training areas

- Professional Barista
- Food & Beverage
- Restaurant Service
- Customer Service
- Hospitality Management
- Housekeeping
- Hotel Operations
- Front Office
- Coffee Roasting
- Coffee Brewing
- Beverage Preparation
- Mixology
- Culinary Basics
- HACCP & Food Safety
- Entrepreneurship in Hospitality

The platform should communicate hospitality, professionalism, coffee culture, career development and premium education.

---

# 9. MODERN UI/UX DIRECTION

The website must not look like a basic CRUD PHP system.

Design inspiration:

- Coursera
- Udemy
- Alison
- Moodle
- LinkedIn Learning
- MasterClass

Do not copy their branding or layouts literally. Use them only as UX references.

## Visual principles

- Generous white space
- Premium cards
- Soft shadows
- Rounded but professional components
- Clear visual hierarchy
- Strong typography
- Large hero sections
- High-quality hospitality imagery
- Subtle gradients
- Smooth hover states
- Micro-interactions
- Skeleton loaders where useful
- Sticky navigation where useful
- Responsive mobile navigation
- Dark mode and light mode
- Accessible contrast
- Clear focus states
- Friendly empty states
- Attractive dashboard widgets

Avoid excessive glassmorphism, giant animations, noisy gradients or childish visual effects.

The website should feel premium, calm, trustworthy and fast.

---

# 10. COLOR SYSTEM

Use the existing Beyond Barista palette as the base:

| Purpose | Color |
|---|---|
| Primary | #4C3103 |
| Secondary | #C0B7C5 |
| Accent | #E29578 |
| Background | #F8F9FA |
| Dark | #1E293B |
| Success | #16A34A |
| Warning | #F59E0B |
| Danger | #DC2626 |

Also define CSS variables so the theme can be maintained centrally.

Example:

```css
:root {
    --color-primary: #4C3103;
    --color-secondary: #C0B7C5;
    --color-accent: #E29578;
    --color-bg: #F8F9FA;
    --color-dark: #1E293B;
    --color-success: #16A34A;
    --color-warning: #F59E0B;
    --color-danger: #DC2626;
}
```

---

# 11. TYPOGRAPHY

Primary fonts:

- Poppins
- Inter

Use Poppins for major headings and Inter for body/interface text where appropriate.

Typography must remain readable on mobile.

Do not load unnecessary font families.

---

# 12. USER ROLES

Implement the following role hierarchy:

1. Visitor
2. Student
3. Instructor
4. Moderator
5. Academy Administrator
6. Super Admin

Each protected area must have server-side authorization checks.

Never rely only on hiding buttons in HTML.

---

# 13. AUTHENTICATION

Implement:

- Registration
- Login
- Logout
- Remember-me option where securely implemented
- Forgot password
- Reset password
- Email verification
- Password strength validation
- Account activation/deactivation
- Session timeout
- Login attempt protection
- Role-based access control

Use:

```php
password_hash($password, PASSWORD_DEFAULT);
```

and:

```php
password_verify($password, $hash);
```

Do not store plain-text passwords.

Prepare the architecture so 2FA can be added later.

Social logins such as Google/Microsoft can be optional integrations, but the core application must work without them.

---

# 14. LANDING PAGE

Build a highly polished homepage with:

1. Premium hero section
2. Strong hospitality headline
3. Primary CTA: Explore Courses
4. Secondary CTA: Become a Student
5. Trust indicators
6. Academy statistics
7. Training categories
8. Featured courses
9. Free courses section
10. Premium courses section
11. Learning benefits
12. Instructor highlights
13. Testimonials
14. Upcoming events
15. Hospitality career/job highlights
16. Blog/articles
17. Newsletter subscription
18. Final CTA
19. Premium footer

Use smooth but lightweight animation.

---

# 15. COURSE CATALOG

Course listing must support:

- Search
- Category filter
- Level filter
- Price filter
- Free/Premium filter
- Duration filter
- Instructor filter
- Rating filter
- Sort by newest
- Sort by popularity
- Sort by price

Use server-side pagination for large datasets.

Course card should display:

- Thumbnail
- Course title
- Short description
- Instructor
- Rating
- Number of students
- Duration
- Difficulty
- Price
- Discount price
- Free/Premium badge
- Progress where applicable
- Wishlist button

---

# 16. COURSE DETAIL PAGE

Include:

- Course hero
- Course title
- Description
- Instructor information
- Rating
- Enrollment count
- Course level
- Duration
- Lessons count
- Language
- Preview lesson/video
- Requirements
- Learning outcomes
- Curriculum
- Course resources
- Certificate information
- Reviews
- Related courses
- Enroll / Buy button

Premium course purchasing must lead into the payment workflow.

---

# 17. LEARNING FLOW

Implement this logical sequence:

```text
Course
  ↓
Modules
  ↓
Lessons
  ↓
Videos / PDFs / Text / Audio / Presentations
  ↓
Assignments
  ↓
Quizzes
  ↓
Final Exam
  ↓
Course Completion
  ↓
Certificate
```

Do not mark a course completed merely because the student opens the final lesson.

Completion must be based on clearly defined course completion rules.

---

# 18. LESSON TYPES

Support:

- Video
- PDF
- Text
- Audio
- Presentation
- Assignment
- Quiz

Every lesson should support:

- Title
- Description
- Sort order
- Published/draft status
- Estimated duration
- Free preview flag
- Required/completion flag

---

# 19. STUDENT FEATURES

Student dashboard:

- Welcome section
- Profile summary
- Enrolled courses
- Continue learning
- Course progress
- Completion percentage
- Certificates
- Wishlist
- Achievements
- Learning streak
- Notifications
- Calendar/events
- Recent activity
- Recommended courses

Student profile:

- Name
- Profile photo
- Bio
- Phone
- Country
- Language
- Education/career information where needed
- Certificates
- Enrollment history

---

# 20. PROGRESS TRACKING

Track progress at lesson level.

Recommended data:

- enrollment_id
- lesson_id
- started_at
- completed_at
- watch/progress percentage where applicable
- last_position for video lessons

The student should be able to click “Continue Learning” and return to the last relevant lesson.

---

# 21. QUIZ SYSTEM

Support:

- Multiple choice
- True / False
- Matching
- Fill in the blank
- Essay
- Randomized questions
- Passing score
- Instant grading
- Timed quizzes/exams
- Question bank
- Attempt limits
- Results history

Record:

- Attempt number
- Started time
- Submitted time
- Score
- Pass/fail
- Answers

Do not expose correct answers before submission unless configured as immediate-feedback mode.

---

# 22. CERTIFICATE SYSTEM

Generate professional PDF certificates containing:

- Unique certificate number
- QR code
- Student name
- Course name
- Completion date
- Certificate issue date
- Instructor signature
- Academy signature
- Academy logo

Create a public verification URL such as:

```text
/certificate/verify/{certificate_number}
```

QR code must point to the verification page.

Certificate status should support:

- Valid
- Revoked
- Expired if future expiry is needed

Keep certificate files outside publicly writable upload directories where practical.

---

# 23. MEMBERSHIP SYSTEM

Implement:

## Free

- Free courses

## Premium Monthly

- Unlimited eligible courses
- Certificates
- Premium resources
- Downloads

## Premium Annual

- Everything in Monthly
- Discounted annual pricing

Membership data must include:

- user_id
- plan_id
- status
- start_date
- end_date
- payment reference
- auto-renew preference where supported

Do not hard-code plan prices inside PHP pages. Store them in the database/settings.

---

# 24. PAYMENT SYSTEM

Structure the application so payment providers can be added safely.

Potential integrations:

- Stripe
- Flutterwave
- PayPal

Also support:

- Coupons
- Discounts
- Invoices
- Receipts
- Payment transaction history
- Failed payments
- Payment callbacks/webhooks where the provider supports them

The database must store provider transaction references.

Never store raw card numbers or CVV information.

The LMS must remain usable for free courses even when payment integrations are not configured.

---

# 25. INSTRUCTOR DASHBOARD

Provide:

- Dashboard overview
- Course management
- Module management
- Lesson management
- Quiz/question management
- Assignment management
- Student list
- Course completion analytics
- Reviews
- Announcements

Where revenue sharing is needed later, design the database for instructor revenue reporting without making it mandatory in version 1.

---

# 26. ADMIN DASHBOARD

Create a premium admin dashboard with:

- Students
- Instructors
- Moderators
- Courses
- Categories
- Enrollments
- Memberships
- Payments
- Coupons
- Certificates
- Reviews
- Blog
- Events
- Jobs
- Notifications
- Reports
- Website CMS
- Settings
- Audit logs

Dashboard widgets should include:

- Total students
- Active students
- Total courses
- Premium courses
- Free courses
- Enrollments
- Revenue
- Certificates issued
- Recent registrations
- Recent payments
- Popular courses

Use Chart.js for visual analytics.

---

# 27. CMS FEATURES

Administrators must be able to manage without changing source code:

- Homepage sections
- Hero headline/subheadline
- Banners
- Testimonials
- FAQs
- Contact details
- Social links
- Footer content
- Site logo
- Favicon
- SEO defaults
- Course categories
- Website settings

Do not make every piece of the website dynamic. Keep the architecture maintainable.

---

# 28. BLOG

Build a hospitality-focused blog.

Categories may include:

- Hospitality Articles
- Coffee Guides
- Career Advice
- Training News
- Barista Tips
- Restaurant Service
- Hotel Operations

Features:

- Rich text content
- Cover image
- Author
- Category
- Tags
- Draft/published state
- Slug
- SEO title
- Meta description
- Social image
- Published date
- Related posts

Use clean SEO-friendly URLs:

```text
/blog/article-slug
```

---

# 29. EVENTS

Support:

- Workshops
- Physical training
- Webinars
- Competitions
- Hospitality seminars

Event fields:

- Title
- Description
- Image
- Location
- Start date
- End date
- Capacity
- Registration status
- Registration fee where applicable
- Organizer

Allow users to register for public events.

---

# 30. JOB BOARD

Create a hospitality job board with:

- Hospitality jobs
- Internships
- Employer portal
- Application tracking

Job fields:

- Job title
- Employer
- Location
- Employment type
- Experience
- Salary/range where applicable
- Deadline
- Description
- Requirements
- Application instructions
- Published status

Keep the employer module separate from the student dashboard architecture.

---

# 31. SOCIAL FEATURES

Provide:

- Course reviews
- Star ratings
- Discussions
- Messaging architecture
- Student profiles
- Leaderboards
- Achievements

For version 1, messaging can use a simple database-backed inbox instead of real-time technology.

Do not create an unnecessarily complicated real-time system for cPanel hosting.

---

# 32. NOTIFICATIONS

Support:

- In-app notifications
- Email notifications

Events that may trigger notifications:

- Account verification
- Password reset
- Enrollment confirmation
- Payment success
- Course completion
- Certificate issuance
- Event registration
- New course publication
- Instructor announcements

Provide an admin notification configuration area.

---

# 33. EMAIL

Build an email service abstraction so SMTP configuration can be changed without editing application logic.

Use PHPMailer or Symfony Mailer if Composer is available on the hosting environment.

Support SMTP configuration through environment variables/config files.

Never hardcode production credentials.

Example configuration variables:

```env
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Beyond Barista Academy"
```

---

# 34. FILE UPLOAD SYSTEM

Support uploads for:

- Course thumbnails
- Course videos metadata/thumbnails
- PDFs
- Audio files
- Presentations
- Profile images
- Blog images
- Event images
- Certificate assets

Security requirements:

- Validate file extension
- Validate MIME type
- Restrict maximum size
- Randomize stored filename
- Never trust uploaded filename
- Prevent executable uploads
- Store user uploads outside the public executable path where practical
- Deny PHP execution inside upload directories
- Generate thumbnails where needed

Do not store uploaded files using the original client filename.

---

# 35. VIDEO STRATEGY

Do not assume large video files should be hosted directly in the normal cPanel account.

Design the LMS so a lesson can reference:

- External video URL
- YouTube/Vimeo video
- Cloud storage/video CDN
- Self-hosted video path for small files

The database should store video provider and video identifier.

Example:

```text
provider = youtube
video_id = ABC123
```

This keeps the LMS portable.

---

# 36. MYSQL DATABASE DESIGN

Use MySQL with InnoDB and utf8mb4.

Minimum major tables:

### Users and security

- users
- user_profiles
- roles
- permissions
- role_permissions
- user_roles
- password_resets
- email_verifications
- login_attempts
- sessions if server-side session persistence is required
- audit_logs

### Learning

- courses
- categories
- course_categories
- course_instructors
- modules
- lessons
- lesson_resources
- enrollments
- lesson_progress
- assignments
- assignment_submissions
- quizzes
- quiz_questions
- quiz_options
- quiz_attempts
- quiz_answers
- course_completion_rules

### Certificates

- certificates
- certificate_templates
- certificate_verifications

### Membership and finance

- membership_plans
- memberships
- coupons
- coupon_redemptions
- orders
- order_items
- payments
- invoices
- receipts

### Content

- blog_posts
- blog_categories
- blog_tags
- blog_post_tags
- events
- event_registrations
- jobs
- job_applications
- testimonials
- faqs

### Social

- reviews
- discussions
- discussion_replies
- messages
- notifications
- wishlists
- achievements
- user_achievements
- learning_streaks

### Settings

- settings
- menu_items
- translations where needed

Add timestamps such as:

- created_at
- updated_at

Use foreign keys consistently.

Add indexes for frequently queried columns such as:

- email
- slug
- status
- course_id
- user_id
- category_id
- certificate_number
- order/payment reference

Do not over-normalize simple settings unnecessarily.

---

# 37. DATABASE RULES

Use PDO prepared statements for all database queries.

Example:

```php
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

Never use:

```php
$sql = "SELECT * FROM users WHERE email = '$email'";
```

Use transactions for financial operations and multi-table operations requiring atomicity.

Example use cases:

- Creating an order and payment record
- Issuing a certificate
- Finalizing an enrollment
- Recording quiz attempt results

---

# 38. ROUTING

Use clean URLs through Apache rewrite rules.

Examples:

```text
/
/courses
/courses/barista-foundation
/login
/register
/student/dashboard
/student/courses
/instructor/dashboard
/admin/dashboard
/certificate/verify/BB-2026-000123
/blog
/blog/how-to-make-perfect-espresso
/events
/jobs
/contact
```

Do not expose internal PHP filenames such as:

```text
course.php?id=123
```

where a clean route can be used.

---

# 39. .HTACCESS REQUIREMENTS

Provide a production-safe .htaccess configuration for:

- Front controller routing
- HTTPS redirection if appropriate
- Directory listing disabled
- Sensitive file blocking
- Upload directory protection
- Security headers where compatible

Block direct access to:

- .env
- config files
- logs
- backups
- SQL dumps
- composer credentials
- internal storage files

Do not blindly copy server directives that may break cPanel hosting.

Keep the .htaccess compatible with Apache 2.4.

---

# 40. SECURITY BASELINE

Implement at minimum:

### SQL injection protection

PDO prepared statements.

### XSS protection

Escape output by default.

### CSRF protection

All state-changing authenticated forms must contain a CSRF token.

### Session security

Use secure cookie settings where HTTPS is available:

- HttpOnly
- Secure
- SameSite=Lax or stricter where appropriate

Regenerate session ID after login.

### Access control

Authorize every protected action on the server.

### Password protection

Use PHP password_hash/password_verify.

### Login abuse

Rate-limit or delay repeated failed logins.

### File upload security

Validate file contents and extensions and prevent executable uploads.

### Error handling

Development:

- Display useful PHP errors

Production:

- Disable detailed error output
- Log errors securely
- Show friendly error pages

### Audit logs

Record sensitive administrative activities such as:

- Login
- Logout where useful
- Role changes
- Course publication
- Payment actions
- Certificate revocation
- User deletion/deactivation
- Settings changes

---

# 41. SEO

Implement:

- Semantic HTML
- Unique title tags
- Meta descriptions
- Canonical URLs
- Open Graph tags
- Twitter/X card metadata
- Schema.org structured data
- XML sitemap
- robots.txt
- Clean URLs
- Image alt text
- Optimized images
- Lazy loading where appropriate
- Breadcrumbs

Schema types may include:

- Organization
- Course
- Article
- Event
- JobPosting
- FAQPage
- BreadcrumbList

Do not generate misleading structured data.

---

# 42. ACCESSIBILITY

Target WCAG 2.1 AA principles.

Implement:

- Semantic HTML
- Keyboard navigation
- Visible focus states
- Accessible forms
- Proper labels
- ARIA only where necessary
- Sufficient contrast
- Error messages understandable by screen readers
- Captions/transcripts for important learning videos where available

---

# 43. PERFORMANCE

Target a fast user experience, especially on mobile networks.

Implement:

- Optimized images
- Responsive images where practical
- Lazy loading
- Minified production CSS/JS where practical
- Browser caching for static assets
- Database indexes
- Pagination
- Avoid N+1 database queries
- Efficient SQL
- Server-side caching for appropriate public content

Do not sacrifice maintainability merely to chase a benchmark score.

---

# 44. DARK MODE / LIGHT MODE

Provide:

- Light mode
- Dark mode
- System preference option if practical

Store user preference safely where useful.

All dashboard components must remain readable in both modes.

---

# 45. MULTILINGUAL SUPPORT

Languages:

- English
- French
- Kinyarwanda

Do not hardcode all interface labels directly in HTML/PHP.

Use translation arrays such as:

```text
resources/lang/en/
resources/lang/fr/
resources/lang/rw/
```

The database content for courses/blogs can initially remain primarily English, while the UI should be structured for translation.

Persist language preference using session/cookie and optionally user profile settings.

---

# 46. SEARCH

Start with MySQL-based search for:

- Courses
- Blog articles
- Events
- Jobs

Do not require Algolia or another external search service for version 1.

Use FULLTEXT indexes where appropriate.

Design the search service so an external search engine can be added later if traffic justifies it.

---

# 47. ANALYTICS

Admin analytics should report:

- Student registrations
- Course enrollments
- Course completion
- Popular courses
- Quiz performance
- Certificate issuance
- Revenue
- Memberships
- Events
- Job applications

Use Chart.js for dashboard visualizations.

Keep analytics queries efficient and aggregate expensive reports when needed.

---

# 48. REPORTING

Allow admins to export appropriate data to:

- CSV
- PDF where useful

Examples:

- Student list
- Enrollment report
- Payment report
- Certificate report
- Course performance
- Event attendance
- Job applications

Do not expose sensitive data in public exports.

---

# 49. ADMIN CMS CONTENT RULE

Anything that staff will likely update frequently should be configurable from admin.

Examples:

- Course content
- Blog posts
- Events
- Testimonials
- FAQs
- Job postings
- Pricing
- Coupons
- Homepage banners

Technical configuration such as database credentials must remain outside the CMS.

---

# 50. ENVIRONMENT CONFIGURATION

Create a `.env.example` file.

Example:

```env
APP_NAME="Beyond Barista Academy"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/beyond-barista-lms

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beyond_barista_lms
DB_USERNAME=root
DB_PASSWORD=

MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Beyond Barista Academy"

PAYMENT_STRIPE_KEY=
PAYMENT_STRIPE_SECRET=
PAYMENT_FLUTTERWAVE_PUBLIC_KEY=
PAYMENT_FLUTTERWAVE_SECRET_KEY=
PAYMENT_PAYPAL_CLIENT_ID=
PAYMENT_PAYPAL_SECRET=
```

The real `.env` must never be committed to public source control.

On cPanel, configure environment variables using the available hosting method or a protected configuration file outside public access.

---

# 51. COMPOSER

Composer is recommended for dependency management.

Potential packages:

- PHPMailer or Symfony Mailer
- Dompdf or mPDF for certificates/PDFs
- chillerlan/php-qrcode or another maintained QR library
- vlucas/phpdotenv if appropriate to the chosen architecture

Do not install packages that are unnecessary.

Keep dependencies lightweight because the production target is shared hosting.

If the selected cPanel host does not provide Composer/SSH, the deployment process must still be documented so the `vendor/` directory can be prepared during deployment through an approved method.

---

# 52. LOCAL DATABASE SETUP

Create the MySQL database using phpMyAdmin or MySQL CLI.

Example:

```sql
CREATE DATABASE beyond_barista_lms
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Create migrations/schema and seeders.

Seed initial data:

- Admin account
- Roles
- Permissions
- Course categories
- Membership plans
- Basic settings
- Sample course
- Sample lessons

Never seed a production password in plain text.

---

# 53. LOCAL TESTING PROCEDURE

After installation:

1. Start Apache.
2. Start MySQL.
3. Open phpMyAdmin.
4. Create database.
5. Import schema/migrations.
6. Configure local environment.
7. Run Composer install if required.
8. Open the application in the browser.
9. Create test users for each role.
10. Test authentication.
11. Test course enrollment.
12. Test lesson completion.
13. Test quizzes.
14. Test certificate generation.
15. Test payment sandbox integrations.
16. Test admin functions.
17. Test mobile layout.
18. Test multilingual interface.
19. Test dark/light mode.
20. Review Apache/PHP/MySQL logs.

---

# 54. ROLE TEST MATRIX

Create dedicated test accounts for:

- Student
- Instructor
- Moderator
- Administrator
- Super Admin

Verify that each role cannot access unauthorized routes.

Example:

A Student must never be able to open:

```text
/admin/users
/admin/payments
/admin/settings
```

simply by typing the URL manually.

---

# 55. PRODUCTION DEPLOYMENT TO CPANEL

Recommended deployment sequence:

## Step 1 — Prepare production database

In cPanel:

- Create MySQL database
- Create database user
- Assign user to database
- Grant required privileges

## Step 2 — Configure PHP

Use cPanel MultiPHP Manager.

Select PHP 8.5 where available and compatible.

If the host does not offer PHP 8.5, use PHP 8.4.

Ensure required extensions are enabled.

Common extensions may include:

- PDO
- pdo_mysql
- mbstring
- openssl
- curl
- fileinfo
- json
- gd
- zip
- intl where needed

## Step 3 — Upload application

Upload the production code to the correct document root.

If the application uses a public directory, configure the domain document root to point to that directory when the hosting plan allows it.

For typical shared hosting where the document root cannot be changed, use a carefully structured public root and keep sensitive folders protected from web access.

## Step 4 — Configure environment

Update production database, mail, URL and payment configuration.

Never copy local passwords into production.

## Step 5 — Import database

Import the production schema and approved seed data using phpMyAdmin.

## Step 6 — Configure .htaccess

Test rewriting and HTTPS.

## Step 7 — Configure permissions

Set only the minimum writable directories required.

## Step 8 — Configure SSL

Enable HTTPS.

## Step 9 — Configure cron jobs

Use cPanel Cron Jobs for scheduled operations.

Examples:

- Expired membership checks
- Scheduled notifications
- Cleanup jobs
- Reminder emails
- Report generation

## Step 10 — Test production

Test every critical user journey before announcing the site as live.

---

# 56. CPANEL DIRECTORY SECURITY

Do not make the following publicly browsable:

```text
/config/
/storage/
/database/
/vendor/
.env
```

Where the hosting architecture makes public access possible, add explicit access protection.

Disable directory indexing.

Protect backup files such as:

```text
*.sql
*.zip
*.tar
*.gz
*.bak
```

Do not leave phpMyAdmin dumps or old application archives inside public directories.

---

# 57. BACKUP STRATEGY

Create a backup plan for:

### Database

- Daily automated backup where hosting supports it
- Weekly off-server backup

### Files

- Uploads
- Certificates
- Application configuration

Do not rely on one backup copy stored on the same cPanel account.

Document restoration procedures.

---

# 58. LOGGING AND MONITORING

Create structured logs for:

- Application errors
- Authentication failures
- Payment callback errors
- Certificate errors
- Scheduled job failures

Do not expose stack traces to normal visitors in production.

Provide a simple admin health page showing:

- PHP version
- Database connectivity
- Writable directories
- Required extensions
- Application environment
- Storage availability where measurable

Never reveal secrets on this page.

---

# 59. ERROR PAGES

Create branded pages for:

- 400
- 401
- 403
- 404
- 419/CSRF error if applicable
- 429
- 500
- 503

The pages should be modern, responsive and consistent with Beyond Barista Academy branding.

---

# 60. API / AJAX ENDPOINTS

Use JSON responses for JavaScript-powered operations such as:

- Search suggestions
- Wishlist toggle
- Lesson progress update
- Quiz submission
- Notification read state
- Admin dashboard filters
- Certificate verification lookup

Return consistent response structures, e.g.:

```json
{
  "success": true,
  "message": "Lesson completed.",
  "data": {}
}
```

Always authorize and validate AJAX requests server-side.

---

# 61. EMAIL TEMPLATES

Create branded email templates for:

- Welcome
- Email verification
- Password reset
- Enrollment confirmation
- Payment receipt
- Certificate issued
- Event registration
- Job application confirmation
- Admin announcements

Templates should be responsive and readable in common email clients.

---

# 62. MODERN DASHBOARD DESIGN

Dashboard layout should include:

- Responsive sidebar
- Top navigation
- Breadcrumbs
- Search
- Notifications
- Profile menu
- KPI cards
- Tables
- Filters
- Charts
- Empty states
- Skeleton loaders where useful
- Toast notifications

On mobile:

- Sidebar becomes drawer
- Tables become horizontally scrollable or card-based
- Cards stack cleanly
- Buttons remain touch-friendly

---

# 63. ACCESSIBILITY + UX DETAILS

Every form must show:

- Label
- Placeholder only when useful
- Validation state
- Error message
- Success message where appropriate

Avoid forms with no feedback.

Buttons must have loading states for slow actions such as:

- Payment initiation
- File upload
- Quiz submission
- Course publishing

Prevent accidental duplicate submissions.

---

# 64. PAYMENT SAFETY RULES

Payment confirmation must not depend solely on a browser redirect.

Where supported, verify the provider callback/webhook server-side.

Validate:

- Transaction ID
- Amount
- Currency
- Order reference
- User/course relationship
- Provider response/signature

Only mark an order as paid after successful server-side verification.

---

# 65. CERTIFICATE SAFETY RULES

Certificate numbers must be unique.

Example format:

```text
BBA-2026-000001
```

The public verification page must reveal only the data intended for verification.

Do not expose private student information.

Certificate generation should be idempotent where possible so repeated requests do not create duplicate certificates.

---

# 66. DATA PRIVACY

Collect only information required for the service.

Provide:

- Privacy Policy
- Terms of Service
- Cookie notice where required
- Account deletion/deactivation workflow

Design for reasonable privacy compliance, but do not claim formal GDPR certification merely because the software includes privacy controls.

---

# 67. ADMIN SETTINGS

Create central settings for:

- Site name
- Logo
- Favicon
- Contact email
- Phone
- Address
- Currency
- Timezone
- Default language
- Default pagination
- Maintenance mode
- Registration enabled/disabled
- Course review settings
- Certificate settings
- Email settings
- Payment settings
- Social links
- SEO defaults

Sensitive secrets should not be displayed back to ordinary administrators.

---

# 68. RWANDA CONTEXT

Design the application for Rwanda first.

Support:

- Rwanda phone numbers
- RWF currency
- Kigali/Rwanda locations
- English, French and Kinyarwanda
- Local hospitality training context
- Local event and job information

Do not force foreign payment assumptions into the core architecture.

Payment providers should be configurable.

---

# 69. TESTING CHECKLIST

## Functional

- Registration
- Login
- Logout
- Password reset
- Email verification
- Course browsing
- Course filtering
- Enrollment
- Lesson access
- Progress tracking
- Quiz submission
- Assignment submission
- Final exam
- Course completion
- Certificate generation
- Certificate verification
- Membership purchase
- Coupon
- Payment callback
- Review submission
- Blog
- Events
- Job applications
- Notifications

## Security

- SQL injection tests
- XSS tests
- CSRF tests
- Authorization tests
- Upload validation
- Session security
- Rate limiting
- Password security
- Direct URL access tests

## UI

- Desktop
- Laptop
- Tablet
- Mobile
- Dark mode
- Light mode
- Keyboard navigation

## Performance

- Large course catalog
- Large student table
- Pagination
- Image loading
- Dashboard queries

---

# 70. ACCEPTANCE CRITERIA

The application is accepted only when:

- It runs correctly in XAMPP.
- It connects to MySQL successfully.
- It can be deployed to cPanel without Node.js.
- Authentication works.
- Roles and permissions work server-side.
- Students can enroll in courses.
- Students can resume lessons.
- Quizzes work.
- Final completion logic works.
- Certificates are generated and verifiable.
- Admin can manage core LMS content.
- Instructor can manage assigned courses.
- Free courses work without payment configuration.
- Premium courses support the configured payment provider.
- English/French/Kinyarwanda UI structure exists.
- SEO fundamentals are implemented.
- HTTPS is supported.
- Sensitive files are protected.
- No plain-text passwords are stored.
- Production error output is disabled.
- The design is responsive.
- The website looks premium rather than like a generic PHP admin template.

---

# 71. DEVELOPMENT PHASES

Build in controlled phases.

## Phase 1 — Foundation

- Project structure
- Routing
- Database connection
- Environment configuration
- Authentication
- Roles/permissions
- Base layout
- Design system

## Phase 2 — Public Website

- Homepage
- About
- Course catalog
- Course detail
- Blog
- Events
- Jobs
- Contact

## Phase 3 — LMS Core

- Courses
- Categories
- Modules
- Lessons
- Enrollment
- Progress tracking
- Student dashboard

## Phase 4 — Assessment

- Assignments
- Quizzes
- Exams
- Completion rules
- Results

## Phase 5 — Certificates

- PDF generation
- QR code
- Public verification

## Phase 6 — Finance

- Membership plans
- Orders
- Coupons
- Payments
- Invoices
- Receipts

## Phase 7 — Instructor/Admin

- Instructor dashboard
- Admin dashboard
- Reports
- CMS
- Analytics

## Phase 8 — Enhancement

- Multilingual UI
- Dark mode
- Notifications
- Social features
- Advanced SEO
- Performance optimization

---

# 72. CLAUDE CODE DEVELOPMENT INSTRUCTIONS

Claude Code must work incrementally.

Do not attempt to generate the entire application blindly in one step.

For every phase:

1. Inspect the existing project.
2. Create or update the necessary files.
3. Explain what changed.
4. Run syntax checks.
5. Run database checks.
6. Test affected routes.
7. Fix errors before moving to the next phase.
8. Keep existing working features intact.

Never overwrite working code unnecessarily.

Prefer small, testable commits/changes.

When creating database changes, provide SQL migration files.

When changing an existing table, do not silently destroy production data.

When a feature is incomplete, mark it explicitly instead of pretending it works.

---

# 73. REQUIRED CODING QUALITY

Code must be:

- Readable
- Modular
- Reusable
- Commented where logic is non-obvious
- Secure
- Testable
- Portable
- Maintainable

Avoid:

- Giant single-file PHP applications
- Repeated SQL everywhere
- Inline CSS throughout HTML
- Inline JavaScript everywhere
- Hard-coded role IDs
- Hard-coded URLs
- Hard-coded prices
- Hard-coded secrets

---

# 74. DATABASE TRANSACTIONS

Use transactions for operations such as:

```text
Create order
→ create order items
→ create payment record
→ enroll student
```

If one required step fails, rollback the transaction where appropriate.

The same principle applies to:

- Certificate issuance
- Membership creation
- Bulk imports
- Administrative destructive operations

---

# 75. DATA IMPORT / EXPORT

Admin should support CSV imports for selected entities where useful.

Potential imports:

- Students
- Courses
- Categories
- Jobs
- Events

Imports must:

- Validate headers
- Validate each row
- Report row errors
- Avoid duplicate records where possible
- Use database transactions for batch operations
- Provide downloadable error reports

Never import unsanitized values directly into SQL.

---

# 76. FUTURE-READY ARCHITECTURE

Design the current platform so it can later add:

- AI tutoring
- AI course assistant
- Mobile app APIs
- Live classes
- Video conferencing
- Advanced analytics
- Corporate training accounts
- Employer recruitment services
- Instructor monetization
- Badges and micro-credentials
- More African languages

Do not build those future features now unless required for version 1.

Prepare interfaces and clean boundaries so future integrations do not require rewriting the entire application.

---

# 77. WHAT NOT TO BUILD IN VERSION 1

Avoid unnecessary complexity such as:

- Microservices
- Kubernetes
- Docker dependency for production
- WebSocket infrastructure
- Redis requirement
- Elasticsearch requirement
- Complex SPA frontend
- Real-time chat server
- AI API dependency for basic learning

The target environment is ordinary XAMPP locally and cPanel in production.

Reliability and maintainability matter more than architectural fashion.

---

# 78. FINAL DELIVERY PACKAGE

The completed project must include:

```text
Source code
Database schema/migrations
Seed data
Composer configuration
Environment example
.htaccess
README
Installation guide
XAMPP setup guide
cPanel deployment guide
Database backup instructions
Cron job instructions
Payment configuration guide
Mail/SMTP configuration guide
Security checklist
Admin user creation guide
```

---

# 79. FINAL IMPLEMENTATION PRINCIPLE

Build this as a serious production LMS, not as a school-demo CRUD application.

The user experience should be premium.

The code should be straightforward.

The database should be structured.

The security model should be strict.

The deployment should be practical.

The application must work on a normal cPanel account without requiring a JavaScript server runtime.

The final website should combine the sophistication of modern online learning platforms with the stability and simplicity of a well-engineered PHP/MySQL application.

**Primary success metric:** a student should be able to discover a course, register, enroll, learn, pass assessments, complete the course, receive a certificate, and verify that certificate online — while administrators can manage the whole ecosystem from a clean dashboard.

---

# 80. HAND-OFF PROMPT FOR CLAUDE CODE

Use this guide as the system-level specification for the project.

Before writing application code:

1. Audit the current repository.
2. Identify whether any existing code can be reused.
3. Propose the final PHP/MySQL architecture.
4. Create the database schema/migrations.
5. Create the authentication and role system.
6. Establish the UI design system.
7. Implement the public website.
8. Implement LMS core.
9. Implement assessments.
10. Implement certificates.
11. Implement memberships and payment abstraction.
12. Implement instructor and admin dashboards.
13. Implement blog/events/jobs.
14. Implement security hardening.
15. Implement testing and deployment documentation.

At each stage, test the feature in XAMPP before continuing.

Do not introduce technologies that make the application difficult or impossible to deploy to conventional cPanel hosting.

The result must be modern, secure, responsive, accessible, scalable and maintainable.
