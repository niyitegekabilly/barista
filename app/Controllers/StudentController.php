<?php

namespace App\Controllers;

use App\Core\Controller;

class StudentController extends Controller
{
    public function dashboard(): void
    {
        $user    = auth();
        $userId  = $user['id'];

        $enrolledCourses = $this->db()->query(
            "SELECT e.*, 
                    c.title, c.title course_title, 
                    c.slug, c.slug course_slug, 
                    c.thumbnail, 
                    c.id course_id,
                    cat.name category_name,
                    cert.certificate_number,
                    (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.enrollment_id = e.id AND lp.is_completed = 1) completed_lessons,
                    (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) total_lessons,
                    COALESCE(
                        ROUND(
                            (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.enrollment_id = e.id AND lp.is_completed = 1) * 100.0 /
                            NULLIF((SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id), 0),
                        0),
                        e.progress_percent,
                        0
                    ) progress_percent
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             LEFT JOIN certificates cert ON cert.enrollment_id = e.id
             WHERE e.user_id = ?
             ORDER BY e.updated_at DESC LIMIT 6",
            [$userId]
        )->fetchAll();

        $certificates = $this->db()->query(
            "SELECT cert.* FROM certificates cert WHERE cert.user_id = ? ORDER BY cert.issue_date DESC",
            [$userId]
        )->fetchAll();

        $stats = [
            'enrolled'     => count($enrolledCourses),
            'completed'    => count(array_filter($enrolledCourses, fn($c) => $c['completed_at'] !== null)),
            'certificates' => count($certificates),
            'wishlist'     => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM wishlists WHERE user_id = ?", [$userId])['cnt'] ?? 0,
        ];

        $this->render('student/dashboard', compact('enrolledCourses', 'certificates', 'stats', 'user'), 'dashboard');
    }

    public function courses(): void
    {
        $userId = auth()['id'];
        $courses = $this->db()->query(
            "SELECT e.*, 
                    c.title, c.title course_title, 
                    c.slug, c.slug course_slug, 
                    c.thumbnail, 
                    c.id course_id,
                    u.name instructor_name,
                    cat.name category_name,
                    cert.certificate_number,
                    (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.enrollment_id = e.id AND lp.is_completed = 1) completed_lessons,
                    (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) total_lessons,
                    COALESCE(
                        ROUND(
                            (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.enrollment_id = e.id AND lp.is_completed = 1) * 100.0 /
                            NULLIF((SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id), 0),
                        0),
                        e.progress_percent,
                        0
                    ) progress_percent
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             LEFT JOIN users u ON c.created_by = u.id
             LEFT JOIN certificates cert ON cert.enrollment_id = e.id
             WHERE e.user_id = ?
             ORDER BY e.created_at DESC",
            [$userId]
        )->fetchAll();

        $this->render('student/courses', compact('courses'), 'dashboard');
    }

    public function certificates(): void
    {
        $userId = auth()['id'];
        $certificates = $this->db()->query(
            "SELECT cert.*, c.title course_title, u.name student_name
             FROM certificates cert
             JOIN courses c ON cert.course_id = c.id
             JOIN users u ON cert.user_id = u.id
             WHERE cert.user_id = ?
             ORDER BY cert.issue_date DESC",
            [$userId]
        )->fetchAll();

        $this->render('student/certificates', compact('certificates'), 'dashboard');
    }

    public function certificateView(Request $request, string $code): void
    {
        $certificate = $this->db()->fetchOne(
            "SELECT cert.*, c.title course_title, u.name student_name
             FROM certificates cert
             JOIN courses c ON cert.course_id = c.id
             JOIN users u ON cert.user_id = u.id
             WHERE cert.certificate_number = ? AND cert.user_id = ?",
            [$code, auth()['id']]
        );

        if (!$certificate) {
            $this->abort(404);
            return;
        }

        $this->render('student/certificate-view', compact('certificate'), 'dashboard');
    }

    public function profile(): void
    {
        $userId = auth()['id'];
        $user   = $this->db()->fetchOne("SELECT u.*, r.slug role_slug FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$userId]);
        $this->render('student/profile', compact('user'), 'dashboard');
    }

    public function updateProfile(): void
    {
        $userId = auth()['id'];
        $name = $this->request->input('name');

        // Update users table
        $this->db()->query(
            "UPDATE users SET name=:name WHERE id=:id",
            ['name' => $name, 'id' => $userId]
        );

        // Update user_profiles table
        $this->db()->query(
            "UPDATE user_profiles SET phone=:phone, city=:city, headline=:headline, bio=:bio WHERE user_id=:user_id",
            [
                'phone' => $this->request->input('phone'),
                'city' => $this->request->input('city'),
                'headline' => $this->request->input('headline'),
                'bio' => $this->request->input('bio'),
                'user_id' => $userId
            ]
        );

        // Update session name
        $_SESSION['user']['name'] = $name;

        $this->flash('success', 'Profile updated successfully.');
        $this->redirect('/student/profile');
    }

    public function wishlist(): void
    {
        $userId = auth()['id'];
        $courses = $this->db()->query(
            "SELECT c.*, cat.name category_name, u.name instructor_name
             FROM wishlists w
             JOIN courses c ON w.course_id = c.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             LEFT JOIN users u ON c.created_by = u.id
             WHERE w.user_id = ?",
            [$userId]
        )->fetchAll();

        $this->render('student/wishlist', compact('courses'), 'dashboard');
    }
}
