<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\BlogPost;
use App\Models\Event;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index(): void
    {
        $featuredCourses = $this->db()->fetchAll(
            "SELECT c.*,
                    cat.name as category_name,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as students_count,
                    (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.course_id = c.id AND r.is_approved = 1) as rating_avg
             FROM courses c
             LEFT JOIN categories cat ON c.category_id = cat.id
             WHERE c.is_published = 1 AND c.is_featured = 1
             ORDER BY c.created_at DESC
             LIMIT 6"
        );

        $categories = Category::getActiveWithCounts();

        // Fetch testimonials
        $testimonials = $this->db()->fetchAll(
            "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 6"
        );

        $latestPosts = BlogPost::getPublished(3);
        $stats = [
            'students'     => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM users WHERE role_id=(SELECT id FROM roles WHERE slug='student')")['cnt'] ?? 0,
            'courses'      => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM courses WHERE is_published=1")['cnt'] ?? 0,
            'certificates' => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM certificates")['cnt'] ?? 0,
            'instructors'  => $this->db()->fetchOne("SELECT COUNT(*) cnt FROM users WHERE role_id=(SELECT id FROM roles WHERE slug='instructor')")['cnt'] ?? 0,
        ];

        $this->render('public/home', compact('featuredCourses', 'categories', 'testimonials', 'latestPosts', 'stats'));
    }

    public function about(): void
    {
        $this->render('public/about');
    }

    public function contact(): void
    {
        $this->render('public/contact');
    }

    public function contactSubmit(): void
    {
        $name    = $this->request->input('name');
        $email   = $this->request->input('email');
        $message = $this->request->input('message');

        // Log or email
        (new \App\Models\AuditLog())->log('contact_form', "Contact from $email: $name");

        $this->flash('success', 'Your message has been received. We will respond within 24 hours.');
        $this->redirect('/contact');
    }

    public function pricing(): void
    {
        $plans = \App\Models\MembershipPlan::getActive();
        $this->render('public/pricing', compact('plans'));
    }
}
