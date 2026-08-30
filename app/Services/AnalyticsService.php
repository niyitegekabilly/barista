<?php

namespace App\Services;

use App\Core\Database;

class AnalyticsService {
    public static function getAdminKPIs(): array {
        $totalStudents = (int) Database::fetchValue("SELECT COUNT(*) FROM users WHERE role_id = 2");
        $totalInstructors = (int) Database::fetchValue("SELECT COUNT(*) FROM users WHERE role_id = 3");
        $totalCourses = (int) Database::fetchValue("SELECT COUNT(*) FROM courses");
        $publishedCourses = (int) Database::fetchValue("SELECT COUNT(*) FROM courses WHERE is_published = 1");
        $totalEnrollments = (int) Database::fetchValue("SELECT COUNT(*) FROM enrollments");
        $certificatesIssued = (int) Database::fetchValue("SELECT COUNT(*) FROM certificates WHERE status = 'valid'");
        $totalRevenue = (float) Database::fetchValue("SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE status = 'completed'");

        // Monthly enrollment trend (last 6 months)
        $enrollmentTrend = Database::fetchAll("
            SELECT DATE_FORMAT(enrolled_at, '%b %Y') as month_label, COUNT(*) as count
            FROM enrollments
            WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m'), DATE_FORMAT(enrolled_at, '%b %Y')
            ORDER BY DATE_FORMAT(enrolled_at, '%Y-%m') ASC
        ");

        // Popular courses
        $popularCourses = Database::fetchAll("
            SELECT c.title, c.slug, c.price, c.is_free,
                   (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enrollments_count,
                   (SELECT COALESCE(AVG(r.rating), 5.0) FROM reviews r WHERE r.course_id = c.id AND r.is_approved = 1) as rating_avg
            FROM courses c
            WHERE c.is_published = 1
            ORDER BY enrollments_count DESC
            LIMIT 5
        ");

        // Recent orders
        $recentOrders = Database::fetchAll("
            SELECT o.*, u.name as user_name, u.email as user_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 6
        ");

        return [
            'total_students' => $totalStudents,
            'total_instructors' => $totalInstructors,
            'total_courses' => $totalCourses,
            'published_courses' => $publishedCourses,
            'total_enrollments' => $totalEnrollments,
            'certificates_issued' => $certificatesIssued,
            'total_revenue' => $totalRevenue,
            'enrollment_trend' => $enrollmentTrend,
            'popular_courses' => $popularCourses,
            'recent_orders' => $recentOrders
        ];
    }
}
