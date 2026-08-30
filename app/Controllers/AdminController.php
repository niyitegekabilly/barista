<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Category;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $stats = [
            'total_users'        => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM users WHERE role_id=(SELECT id FROM roles WHERE slug='student')")['cnt'] ?? 0,
            'new_users_week'     => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
            'total_courses'      => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM courses WHERE is_published=1")['cnt'] ?? 0,
            'pending_courses'    => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM courses WHERE is_published=0")['cnt'] ?? 0,
            'revenue_month'      => $this->db()->fetchOne("SELECT SUM(total_amount) total FROM orders WHERE status='completed' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")['total'] ?? 0,
            'revenue_total'      => $this->db()->fetchOne("SELECT SUM(total_amount) total FROM orders WHERE status='completed'")['total'] ?? 0,
            'total_certificates' => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM certificates")['cnt'] ?? 0,
        ];

        $recent_orders = $this->db()->query(
            "SELECT o.*, u.name student_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8"
        )->fetchAll();

        $pending_courses = $this->db()->query(
            "SELECT c.*, u.name instructor_name FROM courses c JOIN users u ON c.created_by = u.id WHERE c.is_published=0 ORDER BY c.created_at DESC LIMIT 5"
        )->fetchAll();

        // Chart data – last 6 months
        $chart_data = $this->buildChartData();

        $this->render('admin/dashboard', compact('stats', 'recent_orders', 'pending_courses', 'chart_data'), 'dashboard');
    }

    // ── Users ──────────────────────────────────────────────────────
    public function users(): void
    {
        $role = $_GET['role'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $where = $role ? "AND r.slug = :role" : '';
        $params = $role ? ['role' => $role] : [];

        $total = $this->db()->fetchOne(
            "SELECT COUNT(*) cnt FROM users u JOIN roles r ON u.role_id = r.id WHERE 1=1 $where",
            $params
        )['cnt'] ?? 0;

        $users = $this->db()->query(
            "SELECT u.*, r.slug role_slug, COUNT(DISTINCT e.id) enrollment_count
             FROM users u JOIN roles r ON u.role_id = r.id
             LEFT JOIN enrollments e ON e.user_id = u.id
             WHERE 1=1 $where GROUP BY u.id ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();

        $pagination = [
            'total_pages'  => ceil($total / $perPage),
            'current_page' => $page,
        ];

        $this->render('admin/users/index', compact('users', 'pagination'), 'dashboard');
    }

    public function suspendUser(Request $request, int $id): void
    {
        $this->db()->query("UPDATE users SET status='suspended' WHERE id = ?", [$id]);
        (new \App\Models\AuditLog())->log('suspend_user', "Admin suspended user ID $id");
        $this->flash('success', 'User suspended.');
        $this->redirect('/admin/users');
    }

    public function activateUser(Request $request, int $id): void
    {
        $this->db()->query("UPDATE users SET status='active' WHERE id = ?", [$id]);
        (new \App\Models\AuditLog())->log('activate_user', "Admin activated user ID $id");
        $this->flash('success', 'User activated.');
        $this->redirect('/admin/users');
    }

    public function createUser(Request $request): void
    {
        $this->render('admin/users/create', [], 'dashboard');
    }

    public function storeUser(Request $request): void
    {
        $name = $this->request->input('name');
        $email = $this->request->input('email');
        $password = $this->request->input('password');
        $status = $this->request->input('status', 'active');

        // Check if email exists
        $existing = $this->db()->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $this->flash('error', 'Email already exists.');
            $this->redirect('/admin/users/create');
            return;
        }

        // Create user
        $this->db()->insert('users', [
            'role_id' => 2,
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Get the last inserted user ID
        $user = $this->db()->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        $userId = $user['id'] ?? null;

        if ($userId) {
            // Create user profile
            $this->db()->insert('user_profiles', [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->flash('success', 'User created successfully.');
        $this->redirect('/admin/users');
    }

    public function editUser(Request $request, int $id): void
    {
        $user = $this->db()->fetchOne("SELECT u.*, up.* FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = ?", [$id]);
        if (!$user) {
            $this->abort(404);
            return;
        }
        $this->render('admin/users/edit', compact('user'), 'dashboard');
    }

    public function updateUser(Request $request, int $id): void
    {
        $this->db()->query("UPDATE users SET name=?, email=?, status=? WHERE id = ?", [
            $this->request->input('name'),
            $this->request->input('email'),
            $this->request->input('status'),
            $id
        ]);
        $this->flash('success', 'User updated.');
        $this->redirect('/admin/users');
    }

    // ── Courses ────────────────────────────────────────────────────
    public function courses(): void
    {
        $status = $_GET['status'] ?? '';
        $where  = $status === 'published' ? "WHERE c.is_published = 1" : ($status === 'draft' ? "WHERE c.is_published = 0" : '');

        $courses = $this->db()->query(
            "SELECT c.*, u.name instructor_name, cat.name category_name, COUNT(DISTINCT e.id) enrollment_count
             FROM courses c JOIN users u ON c.created_by = u.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             LEFT JOIN enrollments e ON e.course_id = c.id
             $where GROUP BY c.id ORDER BY c.created_at DESC",
            $status ? ['status' => $status] : []
        )->fetchAll();

        $this->render('admin/courses/index', compact('courses'), 'dashboard');
    }

    public function approveCourse(Request $request, int $id): void
    {
        $this->db()->query("UPDATE courses SET is_published=1 WHERE id = ?", [$id]);
        (new \App\Models\AuditLog())->log('approve_course', "Admin approved course ID $id");
        $this->flash('success', 'Course approved and published.');
        $this->redirect('/admin/courses');
    }

    public function rejectCourse(Request $request, int $id): void
    {
        $this->db()->query("UPDATE courses SET is_published=0 WHERE id = ?", [$id]);
        (new \App\Models\AuditLog())->log('reject_course', "Admin rejected course ID $id");
        $this->flash('error', 'Course rejected.');
        $this->redirect('/admin/courses');
    }

    public function unpublishCourse(Request $request, int $id): void
    {
        $this->db()->query("UPDATE courses SET is_published=0 WHERE id = ?", [$id]);
        $this->flash('success', 'Course unpublished.');
        $this->redirect('/admin/courses');
    }

    // ── Categories ─────────────────────────────────────────────────
    public function categories(): void
    {
        $categories = $this->db()->query(
            "SELECT cat.*, COUNT(c.id) course_count FROM categories cat LEFT JOIN courses c ON c.category_id = cat.id GROUP BY cat.id ORDER BY cat.name"
        )->fetchAll();

        $this->render('admin/categories/index', compact('categories'), 'dashboard');
    }

    public function storeCategory(): void
    {
        $this->db()->insert('categories', [
            'name'        => $this->request->input('name'),
            'slug'        => strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($this->request->input('name')))),
            'description' => $this->request->input('description'),
            'icon'        => $this->request->input('icon', 'bi-cup-hot'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->flash('success', 'Category added.');
        $this->redirect('/admin/categories');
    }

    public function updateCategory(Request $request, int $id): void
    {
        $this->db()->update('categories', [
            'name'        => $this->request->input('name'),
            'description' => $this->request->input('description'),
            'icon'        => $this->request->input('icon'),
        ], ['id' => $id]);
        $this->flash('success', 'Category updated.');
        $this->redirect('/admin/categories');
    }

    public function deleteCategory(Request $request, int $id): void
    {
        $this->db()->query("DELETE FROM categories WHERE id = ?", [$id]);
        $this->flash('success', 'Category deleted.');
        $this->redirect('/admin/categories');
    }

    // ── Orders ─────────────────────────────────────────────────────
    public function orders(): void
    {
        $orders = $this->db()->query(
            "SELECT o.*, u.name student_name, p.payment_method
             FROM orders o JOIN users u ON o.user_id = u.id
             LEFT JOIN payments p ON p.order_id = o.id
             ORDER BY o.created_at DESC"
        )->fetchAll();

        $summary = [
            'total_revenue' => $this->db()->fetchOne("SELECT SUM(total_amount) total FROM orders WHERE status='completed'")['total'] ?? 0,
            'total_orders'  => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM orders")['cnt'] ?? 0,
            'pending_orders'=> $this->db()->fetchOne("SELECT COUNT(*) cnt FROM orders WHERE status='pending'")['cnt'] ?? 0,
        ];

        $this->render('admin/orders', compact('orders', 'summary'), 'dashboard');
    }

    // ── Coupons ────────────────────────────────────────────────────
    public function coupons(): void
    {
        $coupons = $this->db()->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
        $this->render('admin/coupons', compact('coupons'), 'dashboard');
    }

    public function storeCoupon(): void
    {
        $this->db()->insert('coupons', [
            'code'           => strtoupper(trim($this->request->input('code'))),
            'discount_type'  => $this->request->input('discount_type'),
            'discount_value' => (float)$this->request->input('discount_value'),
            'max_uses'       => (int)$this->request->input('max_uses', 100),
            'min_spend'      => (float)$this->request->input('min_spend', 0),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $this->flash('success', 'Coupon created.');
        $this->redirect('/admin/coupons');
    }

    public function toggleCoupon(Request $request, int $id): void
    {
        $this->db()->query("UPDATE coupons SET is_active = NOT is_active WHERE id = ?", [$id]);
        $this->flash('success', 'Coupon status updated.');
        $this->redirect('/admin/coupons');
    }

    public function deleteCoupon(Request $request, int $id): void
    {
        $this->db()->query("DELETE FROM coupons WHERE id = ?", [$id]);
        $this->flash('success', 'Coupon deleted.');
        $this->redirect('/admin/coupons');
    }

    // ── Blog ───────────────────────────────────────────────────────
    public function blog(): void
    {
        $posts = $this->db()->query(
            "SELECT p.*, c.name category_name, u.name author_name
             FROM blog_posts p JOIN blog_categories c ON p.category_id = c.id JOIN users u ON p.user_id = u.id
             ORDER BY p.created_at DESC"
        )->fetchAll();

        $this->render('admin/blog', compact('posts'), 'dashboard');
    }

    public function deleteBlogPost(Request $request, int $id): void
    {
        $this->db()->query("DELETE FROM blog_posts WHERE id = ?", [$id]);
        $this->flash('success', 'Blog post deleted.');
        $this->redirect('/admin/blog');
    }

    // ── Settings ───────────────────────────────────────────────────
    public function settings(): void
    {
        $settings = $this->db()->query("SELECT * FROM settings")->fetchAll();
        $this->render('admin/settings', compact('settings'), 'dashboard');
    }

    public function updateSettings(): void
    {
        foreach ($_POST as $key => $value) {
            if ($key === '_csrf') continue;
            $this->db()->query("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?", [$key, $value, $value]);
        }
        $this->flash('success', 'Settings updated.');
        $this->redirect('/admin/settings');
    }

    // ── Audit Logs ─────────────────────────────────────────────────
    public function auditLogs(): void
    {
        $userId   = $_GET['user_id'] ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 30;
        $offset   = ($page - 1) * $perPage;

        $where = $userId ? "WHERE al.user_id = :uid" : '';
        $params = $userId ? ['uid' => $userId] : [];

        $total = $this->db()->fetchOne("SELECT COUNT(*) cnt FROM audit_logs al $where", $params)['cnt'] ?? 0;
        $logs  = $this->db()->query(
            "SELECT al.*, u.name user_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id $where ORDER BY al.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();

        $users = $this->db()->query("SELECT id, name FROM users ORDER BY name")->fetchAll();
        $pagination = ['total_pages' => ceil($total / $perPage), 'current_page' => $page];

        $this->render('admin/audit-logs', compact('logs', 'users', 'pagination'), 'dashboard');
    }

    // ── Private Helpers ────────────────────────────────────────────
    private function buildChartData(): array
    {
        $months = [];
        $enrollmentData = [];
        $revenueData    = [];

        for ($i = 5; $i >= 0; $i--) {
            $d    = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime("-$i months"));
            $enrollmentData[] = $this->db()->fetchOne("SELECT COUNT(*) cnt FROM enrollments WHERE DATE_FORMAT(enrolled_at,'%Y-%m')=?", [$d])['cnt'] ?? 0;
            $revenueData[]    = $this->db()->fetchOne("SELECT COALESCE(SUM(total_amount),0) total FROM orders WHERE status='completed' AND DATE_FORMAT(created_at,'%Y-%m')=?", [$d])['total'] ?? 0;
        }

        $categories = $this->db()->query(
            "SELECT cat.name, COUNT(c.id) * 1000 revenue FROM categories cat
             LEFT JOIN courses c ON c.category_id = cat.id
             GROUP BY cat.id ORDER BY cat.sort_order ASC LIMIT 5"
        )->fetchAll();

        return [
            'enrollment_labels' => $months,
            'enrollment_data'   => $enrollmentData,
            'revenue_labels'    => array_column($categories, 'name'),
            'revenue_data'      => array_column($categories, 'revenue'),
        ];
    }

    private function uploadAsset(string $field, string $folder): string
    {
        $file   = $_FILES[$field];
        $ext    = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name   = uniqid() . '.' . $ext;
        $target = dirname(__DIR__, 2) . '/public/uploads/' . $folder . '/';
        if (!is_dir($target)) mkdir($target, 0755, true);
        move_uploaded_file($file['tmp_name'], $target . $name);
        return $folder . '/' . $name;
    }
}
