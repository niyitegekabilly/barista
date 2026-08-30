<?php

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\CourseController;
use App\Controllers\CheckoutController;
use App\Controllers\ClassroomController;
use App\Controllers\StudentController;
use App\Controllers\QuizController;
use App\Controllers\InstructorController;
use App\Controllers\AdminController;
use App\Controllers\BlogController;
use App\Controllers\EventController;
use App\Controllers\JobController;
use App\Controllers\CertificateController;
use App\Controllers\ApiController;

/** @var Router $router */

// ─── Public Routes ────────────────────────────────────────────────────────────
$router->get('/',                   [HomeController::class, 'index']);
$router->get('/about',              [HomeController::class, 'about']);
$router->get('/pricing',            [HomeController::class, 'pricing']);
$router->get('/contact',            [HomeController::class, 'contact']);
$router->post('/contact',           [HomeController::class, 'contactSubmit'], ['csrf']);

// Courses
$router->get('/courses',               [CourseController::class, 'index']);
$router->get('/courses/{slug}',        [CourseController::class, 'show']);
$router->get('/course/{slug}',         [CourseController::class, 'show']);
$router->post('/courses/enroll/{id}',  [CourseController::class, 'enroll'], ['auth', 'csrf']);
$router->post('/course/enroll/{id}',   [CourseController::class, 'enroll'], ['auth', 'csrf']);

// Blog
$router->get('/blog',               [BlogController::class, 'index']);
$router->get('/blog/{slug}',        [BlogController::class, 'show']);

// Events & Jobs
$router->get('/events',             [EventController::class, 'index']);
$router->get('/jobs',               [JobController::class, 'index']);

// Certificate Verification (public)
$router->get('/certificate/verify',        [CertificateController::class, 'verify']);
$router->get('/certificate/verify/{code}', [CertificateController::class, 'verify']);

// ─── Auth Routes ──────────────────────────────────────────────────────────────
$router->get('/login',              [AuthController::class, 'showLogin']);
$router->post('/login',             [AuthController::class, 'login'], ['csrf']);
$router->get('/register',           [AuthController::class, 'showRegister']);
$router->post('/register',          [AuthController::class, 'register'], ['csrf']);
$router->get('/forgot-password',    [AuthController::class, 'showForgotPassword']);
$router->get('/logout',              [AuthController::class, 'logout']);
$router->post('/logout',             [AuthController::class, 'logout']);

// ─── Checkout ─────────────────────────────────────────────────────────────────
$router->get('/checkout/{slug}',       [CheckoutController::class, 'show'],         ['auth']);
$router->get('/checkout',               [CheckoutController::class, 'showMembership'], ['auth']);
$router->post('/checkout/coupon',      [CheckoutController::class, 'applyCoupon'],  ['auth', 'csrf']);
$router->post('/checkout/initiate',    [CheckoutController::class, 'initiate'],     ['auth', 'csrf']);
$router->post('/payment/callback',     [CheckoutController::class, 'callback']);

// ─── Student Dashboard ────────────────────────────────────────────────────────
$router->get('/student/dashboard',                          [StudentController::class, 'dashboard'],        ['auth', 'role:student,instructor,admin']);
$router->get('/student/courses',                            [StudentController::class, 'courses'],          ['auth']);
$router->get('/student/certificates',                       [StudentController::class, 'certificates'],     ['auth']);
$router->get('/student/certificates/{code}',                [StudentController::class, 'certificateView'],  ['auth']);
$router->get('/student/profile',                            [StudentController::class, 'profile'],          ['auth']);
$router->post('/student/profile/update',                    [StudentController::class, 'updateProfile'],    ['auth', 'csrf']);
$router->get('/student/wishlist',                           [StudentController::class, 'wishlist'],         ['auth']);

// Classroom
$router->get('/student/classroom/{courseSlug}',             [ClassroomController::class, 'show'],           ['auth']);
$router->get('/student/classroom/{courseSlug}/{lessonId}',  [ClassroomController::class, 'show'],           ['auth']);
$router->post('/student/lesson/complete',                   [ClassroomController::class, 'completeLesson'], ['auth', 'csrf']);

// Quiz
$router->get('/student/quiz/{quizId}',                      [QuizController::class, 'show'],                ['auth']);
$router->post('/student/quiz/{quizId}/submit',              [QuizController::class, 'submit'],              ['auth', 'csrf']);

// ─── Instructor Routes ────────────────────────────────────────────────────────
$router->get('/instructor/dashboard',                        [InstructorController::class, 'dashboard'],     ['auth', 'role:instructor,admin']);
$router->get('/instructor/courses',                          [InstructorController::class, 'courses'],       ['auth', 'role:instructor,admin']);
$router->get('/instructor/courses/create',                   [InstructorController::class, 'createCourse'],  ['auth', 'role:instructor,admin']);
$router->post('/instructor/courses/store',                   [InstructorController::class, 'storeCourse'],   ['auth', 'role:instructor,admin', 'csrf']);
$router->get('/instructor/courses/{id}/edit',                [InstructorController::class, 'editCourse'],    ['auth', 'role:instructor,admin']);
$router->match(['POST', 'PUT'], '/instructor/courses/{id}/update', [InstructorController::class, 'updateCourse'], ['auth', 'role:instructor,admin', 'csrf']);
$router->get('/instructor/courses/{id}/curriculum',          [InstructorController::class, 'curriculum'],    ['auth', 'role:instructor,admin']);
$router->post('/instructor/courses/{courseId}/modules/store',[InstructorController::class, 'storeModule'],  ['auth', 'role:instructor,admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/instructor/modules/{moduleId}/delete', [InstructorController::class, 'deleteModule'], ['auth', 'role:instructor,admin', 'csrf']);
$router->post('/instructor/lessons/store',                   [InstructorController::class, 'storeLesson'],   ['auth', 'role:instructor,admin', 'csrf']);
$router->get('/instructor/lessons/{id}/edit',                 [InstructorController::class, 'editLesson'],    ['auth', 'role:instructor,admin']);
$router->match(['POST', 'PUT'], '/instructor/lessons/{id}/update', [InstructorController::class, 'updateLesson'], ['auth', 'role:instructor,admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/instructor/lessons/{lessonId}/delete', [InstructorController::class, 'deleteLesson'], ['auth', 'role:instructor,admin', 'csrf']);
$router->get('/instructor/students',                         [InstructorController::class, 'students'],            ['auth', 'role:instructor,admin']);
$router->get('/instructor/reviews',                          [InstructorController::class, 'reviews'],             ['auth', 'role:instructor,admin']);
$router->get('/instructor/quizzes',                          [InstructorController::class, 'quizzes'],             ['auth', 'role:instructor,admin']);
$router->post('/instructor/courses/{courseId}/quizzes/store',[InstructorController::class, 'storeQuiz'],            ['auth', 'role:instructor,admin', 'csrf']);
$router->get('/instructor/quizzes/{quizId}/edit',            [InstructorController::class, 'editQuiz'],             ['auth', 'role:instructor,admin']);
$router->match(['POST', 'PUT'], '/instructor/quizzes/{quizId}/update', [InstructorController::class, 'updateQuiz'], ['auth', 'role:instructor,admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/instructor/quizzes/{quizId}/delete', [InstructorController::class, 'deleteQuiz'], ['auth', 'role:instructor,admin', 'csrf']);
$router->post('/instructor/quizzes/{quizId}/generate-ai',    [InstructorController::class, 'generateAiQuestions'],  ['auth', 'role:instructor,admin', 'csrf']);
$router->post('/instructor/quizzes/{quizId}/questions/store',[InstructorController::class, 'storeQuestion'],        ['auth', 'role:instructor,admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/instructor/questions/{questionId}/delete', [InstructorController::class, 'deleteQuestion'], ['auth', 'role:instructor,admin', 'csrf']);

// ─── Admin Routes ─────────────────────────────────────────────────────────────
$router->get('/admin/dashboard',             [AdminController::class, 'dashboard'],      ['auth', 'role:admin']);

// Users
$router->get('/admin/users',                 [AdminController::class, 'users'],           ['auth', 'role:admin']);
$router->get('/admin/users/create',          [AdminController::class, 'createUser'],      ['auth', 'role:admin']);
$router->post('/admin/users/store',          [AdminController::class, 'storeUser'],       ['auth', 'role:admin', 'csrf']);
$router->get('/admin/users/{id}/edit',       [AdminController::class, 'editUser'],        ['auth', 'role:admin']);
$router->post('/admin/users/{id}/update',    [AdminController::class, 'updateUser'],      ['auth', 'role:admin', 'csrf']);
$router->post('/admin/users/{id}/suspend',   [AdminController::class, 'suspendUser'],     ['auth', 'role:admin', 'csrf']);
$router->post('/admin/users/{id}/activate',  [AdminController::class, 'activateUser'],    ['auth', 'role:admin', 'csrf']);

// Courses
$router->get('/admin/courses',               [AdminController::class, 'courses'],          ['auth', 'role:admin']);
$router->post('/admin/courses/{id}/approve', [AdminController::class, 'approveCourse'],    ['auth', 'role:admin', 'csrf']);
$router->post('/admin/courses/{id}/reject',  [AdminController::class, 'rejectCourse'],     ['auth', 'role:admin', 'csrf']);
$router->post('/admin/courses/{id}/unpublish',[AdminController::class, 'unpublishCourse'], ['auth', 'role:admin', 'csrf']);

// Categories
$router->get('/admin/categories',            [AdminController::class, 'categories'],       ['auth', 'role:admin']);
$router->post('/admin/categories/store',     [AdminController::class, 'storeCategory'],    ['auth', 'role:admin', 'csrf']);
$router->match(['POST', 'PUT'], '/admin/categories/{id}/update', [AdminController::class, 'updateCategory'], ['auth', 'role:admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'], ['auth', 'role:admin', 'csrf']);

// Orders & Coupons
$router->get('/admin/orders',                [AdminController::class, 'orders'],           ['auth', 'role:admin']);
$router->get('/admin/coupons',               [AdminController::class, 'coupons'],          ['auth', 'role:admin']);
$router->post('/admin/coupons/store',        [AdminController::class, 'storeCoupon'],      ['auth', 'role:admin', 'csrf']);
$router->post('/admin/coupons/{id}/toggle',  [AdminController::class, 'toggleCoupon'],     ['auth', 'role:admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/admin/coupons/{id}/delete', [AdminController::class, 'deleteCoupon'], ['auth', 'role:admin', 'csrf']);

// Blog
$router->get('/admin/blog',                  [AdminController::class, 'blog'],             ['auth', 'role:admin']);
$router->match(['POST', 'DELETE'], '/admin/blog/{id}/delete', [AdminController::class, 'deleteBlogPost'], ['auth', 'role:admin', 'csrf']);

// Settings & Audit
$router->get('/admin/settings',              [AdminController::class, 'settings'],         ['auth', 'role:admin']);
$router->match(['POST', 'PUT'], '/admin/settings/update', [AdminController::class, 'updateSettings'], ['auth', 'role:admin', 'csrf']);
$router->get('/admin/audit-logs',            [AdminController::class, 'auditLogs'],        ['auth', 'role:admin']);

// ─── API / AJAX Routes ────────────────────────────────────────────────────────
$router->post('/api/wishlist/toggle',        [ApiController::class, 'toggleWishlist'],  ['auth', 'csrf']);
$router->post('/api/lesson/complete',        [ApiController::class, 'trackLesson'],     ['auth', 'csrf']);
$router->get('/api/courses/search',          [ApiController::class, 'searchCourses']);
