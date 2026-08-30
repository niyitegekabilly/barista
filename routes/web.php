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

// Courses & Categories
$router->get('/courses',                       [CourseController::class, 'index']);
$router->get('/courses/category/{slug}',        [CourseController::class, 'category']);
$router->get('/category/{slug}',                 [CourseController::class, 'category']);
$router->get('/courses/{slug}',                [CourseController::class, 'show']);
$router->get('/course/{slug}',                 [CourseController::class, 'show']);
$router->post('/courses/enroll/{id}',          [CourseController::class, 'enroll'], ['auth', 'csrf']);
$router->post('/course/enroll/{id}',           [CourseController::class, 'enroll'], ['auth', 'csrf']);

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
$router->post('/instructor/courses/{id}/submit-review',      [InstructorController::class, 'submitForReview'],['auth', 'role:instructor,admin', 'csrf']);
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

// ─── Invitation Onboarding (Public) ─────────────────────────────────────────
$router->get('/invite/accept/{token}',        [\App\Controllers\InvitationController::class, 'showAccept']);
$router->post('/invite/accept/{token}',       [\App\Controllers\InvitationController::class, 'processAccept'], ['csrf']);

// ─── Admin Routes ─────────────────────────────────────────────────────────────
$router->get('/admin/dashboard',             [AdminController::class, 'dashboard'],      ['auth', 'role:admin,super_admin']);

// Users & IAM
$router->get('/admin/users',                         [\App\Controllers\AdminUserController::class, 'index'],               ['auth', 'role:admin,super_admin']);
$router->get('/admin/users/create',                  [\App\Controllers\AdminUserController::class, 'create'],              ['auth', 'role:admin,super_admin']);
$router->post('/admin/users/store',                  [\App\Controllers\AdminUserController::class, 'store'],               ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/users/invite',                 [\App\Controllers\AdminUserController::class, 'sendInvite'],          ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/users/bulk',                   [\App\Controllers\AdminUserController::class, 'bulkAction'],          ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/users/import/preview',          [\App\Controllers\AdminUserController::class, 'importCsvPreview'],   ['auth', 'role:admin,super_admin']);
$router->post('/admin/users/import/process',          [\App\Controllers\AdminUserController::class, 'importCsvProcess'],   ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/admin/users/export',                  [\App\Controllers\AdminUserController::class, 'exportCsv'],          ['auth', 'role:admin,super_admin']);
$router->get('/admin/users/{id}',                    [\App\Controllers\AdminUserController::class, 'show'],               ['auth', 'role:admin,super_admin']);
$router->get('/admin/users/{id}/edit',               [\App\Controllers\AdminUserController::class, 'edit'],               ['auth', 'role:admin,super_admin']);
$router->post('/admin/users/{id}/update',            [\App\Controllers\AdminUserController::class, 'update'],             ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/users/{id}/notes',             [\App\Controllers\AdminUserController::class, 'addNote'],            ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/users/{id}/enroll',            [\App\Controllers\AdminUserController::class, 'enrollCourse'],       ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/users/{id}/drop-course/{courseId}', [\App\Controllers\AdminUserController::class, 'dropCourse'],     ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/users/{id}/reset-password',    [\App\Controllers\AdminUserController::class, 'resetPassword'],       ['auth', 'role:admin,super_admin', 'csrf']);

// Roles & Permissions Matrix
$router->get('/admin/roles',                         [\App\Controllers\AdminUserController::class, 'rolesIndex'],          ['auth', 'role:admin,super_admin']);
$router->post('/admin/roles/{id}/update',            [\App\Controllers\AdminUserController::class, 'roleUpdate'],          ['auth', 'role:admin,super_admin', 'csrf']);

// Cohorts & Batches
$router->get('/admin/cohorts',                       [\App\Controllers\AdminUserController::class, 'cohortsIndex'],        ['auth', 'role:admin,super_admin']);
$router->post('/admin/cohorts/store',                [\App\Controllers\AdminUserController::class, 'cohortStore'],         ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/admin/cohorts/{id}',                  [\App\Controllers\AdminUserController::class, 'cohortShow'],          ['auth', 'role:admin,super_admin']);

// Courses & Approval (real lifecycle/approval workflow — AdminCourseController).
// Gated by granular `permission:` middleware (not a fixed role list) so an
// admin can grant/revoke these abilities per-role via /admin/roles without a
// code change — e.g. granting `courses.publish` to Instructor really unlocks
// the publish route for them, per the spec's "unless explicitly permitted".
$router->get('/admin/courses',                      [\App\Controllers\AdminCourseController::class, 'index'],          ['auth', 'permission:courses.view']);
$router->post('/admin/courses/bulk',                [\App\Controllers\AdminCourseController::class, 'bulkAction'],     ['auth', 'permission:courses.view', 'csrf']);
$router->get('/admin/courses/{id}',                 [\App\Controllers\AdminCourseController::class, 'show'],           ['auth', 'permission:courses.view']);
$router->post('/admin/courses/{id}/start-review',   [\App\Controllers\AdminCourseController::class, 'startReview'],    ['auth', 'permission:courses.review', 'csrf']);
$router->post('/admin/courses/{id}/approve',        [\App\Controllers\AdminCourseController::class, 'approve'],        ['auth', 'permission:courses.review', 'csrf']);
$router->post('/admin/courses/{id}/reject',         [\App\Controllers\AdminCourseController::class, 'reject'],         ['auth', 'permission:courses.review', 'csrf']);
$router->post('/admin/courses/{id}/request-changes',[\App\Controllers\AdminCourseController::class, 'requestChanges'], ['auth', 'permission:courses.review', 'csrf']);
$router->post('/admin/courses/{id}/publish',        [\App\Controllers\AdminCourseController::class, 'publish'],        ['auth', 'permission:courses.publish', 'csrf']);
$router->post('/admin/courses/{id}/schedule',       [\App\Controllers\AdminCourseController::class, 'schedule'],       ['auth', 'permission:courses.publish', 'csrf']);
$router->post('/admin/courses/{id}/unpublish',      [\App\Controllers\AdminCourseController::class, 'unpublish'],      ['auth', 'permission:courses.publish', 'csrf']);
$router->post('/admin/courses/{id}/archive',        [\App\Controllers\AdminCourseController::class, 'archive'],        ['auth', 'permission:courses.archive', 'csrf']);
$router->post('/admin/courses/{id}/restore',        [\App\Controllers\AdminCourseController::class, 'restore'],        ['auth', 'permission:courses.archive', 'csrf']);

// Categories & Taxonomy Hub
$router->get('/admin/categories',                     [\App\Controllers\AdminCategoryController::class, 'index'],              ['auth', 'role:admin,super_admin']);
$router->post('/admin/categories/store',              [\App\Controllers\AdminCategoryController::class, 'store'],              ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/admin/categories/export',              [\App\Controllers\AdminCategoryController::class, 'exportCsv'],          ['auth', 'role:admin,super_admin']);
$router->post('/admin/categories/import/preview',     [\App\Controllers\AdminCategoryController::class, 'importCsvPreview'],   ['auth', 'role:admin,super_admin']);
$router->post('/admin/categories/import/process',     [\App\Controllers\AdminCategoryController::class, 'importCsvProcess'],   ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/categories/bulk',               [\App\Controllers\AdminCategoryController::class, 'bulkAction'],          ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/admin/categories/{id}',                [\App\Controllers\AdminCategoryController::class, 'show'],               ['auth', 'role:admin,super_admin']);
$router->post('/admin/categories/{id}/update',        [\App\Controllers\AdminCategoryController::class, 'update'],             ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/categories/{id}/duplicate',     [\App\Controllers\AdminCategoryController::class, 'duplicate'],          ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/admin/categories/{id}/delete-prompt',  [\App\Controllers\AdminCategoryController::class, 'deletePrompt'],        ['auth', 'role:admin,super_admin']);
$router->post('/admin/categories/{id}/delete',        [\App\Controllers\AdminCategoryController::class, 'delete'],              ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/categories/{id}/reassign-course',[\App\Controllers\AdminCategoryController::class, 'reassignCourse'],   ['auth', 'role:admin,super_admin', 'csrf']);

// Tags Management
$router->get('/admin/tags',                           [\App\Controllers\AdminTagController::class, 'index'],                   ['auth', 'role:admin,super_admin']);
$router->post('/admin/tags/store',                    [\App\Controllers\AdminTagController::class, 'store'],                   ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/tags/{id}/update',              [\App\Controllers\AdminTagController::class, 'update'],                  ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/tags/{id}/delete',              [\App\Controllers\AdminTagController::class, 'delete'],                  ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/api/tags/search',                      [\App\Controllers\AdminTagController::class, 'apiSearch']);

// Public Checkout & Invoices
$router->get('/checkout/{slug}',                      [\App\Controllers\CheckoutController::class, 'show'],                    ['auth']);
$router->post('/checkout/process',                    [\App\Controllers\CheckoutController::class, 'process'],                 ['auth', 'csrf']);
$router->post('/api/checkout/validate-coupon',        [\App\Controllers\CheckoutController::class, 'validateCoupon'],          ['auth']);
$router->get('/checkout/success/{orderNumber}',       [\App\Controllers\CheckoutController::class, 'success'],                 ['auth']);
$router->get('/checkout/failed/{orderNumber}',        [\App\Controllers\CheckoutController::class, 'failed'],                  ['auth']);
$router->get('/invoice/{invoiceNumber}',              [\App\Controllers\InvoiceController::class, 'show'],                     ['auth']);
$router->get('/receipt/{receiptNumber}',              [\App\Controllers\InvoiceController::class, 'receipt'],                  ['auth']);
$router->post('/api/webhooks/payment/{gateway}',      [\App\Controllers\WebhookController::class, 'handle']);

// Admin Finance & Revenue Hub
$router->get('/admin/finance',                        [\App\Controllers\AdminFinanceController::class, 'dashboard'],            ['auth', 'role:admin,super_admin']);
$router->get('/admin/finance/reports',                [\App\Controllers\AdminFinanceController::class, 'reports'],              ['auth', 'role:admin,super_admin']);
$router->get('/admin/finance/ledger',                 [\App\Controllers\AdminFinanceController::class, 'ledger'],               ['auth', 'role:admin,super_admin']);

// Admin Orders Management
$router->get('/admin/orders',                         [\App\Controllers\AdminOrderController::class, 'index'],                  ['auth', 'role:admin,super_admin']);
$router->get('/admin/orders/export',                  [\App\Controllers\AdminOrderController::class, 'export'],                 ['auth', 'role:admin,super_admin']);
$router->get('/admin/orders/{id}',                    [\App\Controllers\AdminOrderController::class, 'show'],                   ['auth', 'role:admin,super_admin']);
$router->post('/admin/orders/{id}/refund',            [\App\Controllers\AdminOrderController::class, 'refund'],                 ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/orders/{id}/cancel',            [\App\Controllers\AdminOrderController::class, 'cancel'],                 ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/orders/{id}/add-note',          [\App\Controllers\AdminOrderController::class, 'addNote'],                ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/orders/{id}/verify-manual',     [\App\Controllers\AdminOrderController::class, 'verifyManualPayment'],    ['auth', 'role:admin,super_admin', 'csrf']);

// Admin Payments Management
$router->get('/admin/payments',                       [\App\Controllers\AdminPaymentController::class, 'index'],                ['auth', 'role:admin,super_admin']);

// Coupons & Promotions Hub
$router->get('/admin/coupons',                        [\App\Controllers\AdminCouponController::class, 'index'],                 ['auth', 'role:admin,super_admin']);
$router->get('/admin/coupons/dashboard',              [\App\Controllers\AdminCouponController::class, 'dashboard'],             ['auth', 'role:admin,super_admin']);
$router->get('/admin/coupons/create',                 [\App\Controllers\AdminCouponController::class, 'create'],                ['auth', 'role:admin,super_admin']);
$router->post('/admin/coupons/store',                 [\App\Controllers\AdminCouponController::class, 'store'],                 ['auth', 'role:admin,super_admin', 'csrf']);
$router->match(['GET', 'POST'], '/admin/coupons/bulk-generate', [\App\Controllers\AdminCouponController::class, 'bulkGenerate'], ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/admin/coupons/redemptions',            [\App\Controllers\AdminCouponController::class, 'redemptions'],          ['auth', 'role:admin,super_admin']);
$router->get('/admin/coupons/export',                 [\App\Controllers\AdminCouponController::class, 'export'],               ['auth', 'role:admin,super_admin']);
$router->get('/admin/coupons/export-redemptions',      [\App\Controllers\AdminCouponController::class, 'exportRedemptions'],    ['auth', 'role:admin,super_admin']);
$router->get('/admin/coupons/{id}',                   [\App\Controllers\AdminCouponController::class, 'show'],                  ['auth', 'role:admin,super_admin']);
$router->get('/admin/coupons/{id}/edit',              [\App\Controllers\AdminCouponController::class, 'edit'],                  ['auth', 'role:admin,super_admin']);
$router->post('/admin/coupons/{id}/update',           [\App\Controllers\AdminCouponController::class, 'update'],                ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/coupons/{id}/duplicate',        [\App\Controllers\AdminCouponController::class, 'duplicate'],             ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/coupons/{id}/toggle',           [\App\Controllers\AdminCouponController::class, 'toggle'],                ['auth', 'role:admin,super_admin', 'csrf']);
$router->post('/admin/coupons/{id}/archive',          [\App\Controllers\AdminCouponController::class, 'archive'],               ['auth', 'role:admin,super_admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/admin/coupons/{id}/delete', [\App\Controllers\AdminCouponController::class, 'delete'],   ['auth', 'role:admin,super_admin', 'csrf']);

// Marketing Campaigns
$router->get('/admin/campaigns',                      [\App\Controllers\AdminCampaignController::class, 'index'],               ['auth', 'role:admin,super_admin']);
$router->post('/admin/campaigns/store',               [\App\Controllers\AdminCampaignController::class, 'store'],               ['auth', 'role:admin,super_admin', 'csrf']);
$router->get('/admin/campaigns/{id}',                 [\App\Controllers\AdminCampaignController::class, 'show'],                ['auth', 'role:admin,super_admin']);
$router->post('/admin/campaigns/{id}/update',          [\App\Controllers\AdminCampaignController::class, 'update'],              ['auth', 'role:admin,super_admin', 'csrf']);
$router->match(['POST', 'DELETE'], '/admin/campaigns/{id}/delete', [\App\Controllers\AdminCampaignController::class, 'delete'], ['auth', 'role:admin,super_admin', 'csrf']);

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
