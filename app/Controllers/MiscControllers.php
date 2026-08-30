<?php

namespace App\Controllers;

use App\Core\Controller;

class BlogController extends Controller
{
    public function index(): void
    {
        $category = $_GET['category'] ?? '';
        $search   = $_GET['q'] ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 9;
        $offset   = ($page - 1) * $perPage;

        $where  = ["bp.is_published = 1"];
        $params = [];
        if ($search) { $where[] = "(bp.title LIKE :q OR bp.excerpt LIKE :q2)"; $params['q'] = "%$search%"; $params['q2'] = "%$search%"; }

        $whereStr = 'WHERE ' . implode(' AND ', $where);
        $total = $this->db()->fetchOne("SELECT COUNT(*) cnt FROM blog_posts bp $whereStr", $params)['cnt'] ?? 0;

        $posts = $this->db()->query(
            "SELECT bp.*, u.name author_name, bc.name category_name 
             FROM blog_posts bp 
             LEFT JOIN users u ON bp.user_id = u.id 
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
             $whereStr 
             ORDER BY bp.published_at DESC 
             LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();

        $pagination = ['total_pages' => ceil($total / $perPage), 'current_page' => $page];

        $this->render('public/blog/index', compact('posts', 'pagination', 'search'), 'main');
    }

    public function show(\App\Core\Request $request, string $slug): void
    {
        $post = $this->db()->fetchOne(
            "SELECT bp.*, u.name author_name FROM blog_posts bp LEFT JOIN users u ON bp.user_id = u.id WHERE bp.slug = ? AND bp.is_published = 1",
            [$slug]
        );
        if (!$post) { $this->abort(404); return; }

        $related = $this->db()->query(
            "SELECT * FROM blog_posts WHERE is_published = 1 AND id != ? ORDER BY published_at DESC LIMIT 3",
            [$post['id']]
        )->fetchAll();

        $this->render('public/blog/show', compact('post', 'related'), 'main');
    }
}

class EventController extends Controller
{
    public function index(): void
    {
        $events = $this->db()->query(
            "SELECT * FROM events WHERE is_published = 1 ORDER BY start_date ASC"
        )->fetchAll();
        $this->render('public/events/index', compact('events'), 'main');
    }
}

class JobController extends Controller
{
    public function index(): void
    {
        $jobs = $this->db()->query(
            "SELECT * FROM jobs WHERE is_published = 1 ORDER BY created_at DESC"
        )->fetchAll();
        $this->render('public/jobs/index', compact('jobs'), 'main');
    }
}

class CertificateController extends Controller
{
    public function verify(?string $code = null): void
    {
        $result = null;
        if ($code) {
            $result = $this->db()->fetchOne(
                "SELECT cert.*, c.title course_title, u.name student_name FROM certificates cert
                 JOIN courses c ON cert.course_id = c.id JOIN users u ON cert.user_id = u.id
                 WHERE cert.certificate_number = ?",
                [$code]
            );
        }
        $this->render('public/certificates/verify', compact('result', 'code'), 'main');
    }
}

class ApiController extends Controller
{
    public function toggleWishlist(): void
    {
        if (!auth()) { $this->json(['success' => false], 401); return; }
        $userId   = auth()['id'];
        $courseId = (int)$this->request->json('course_id');

        $exists = $this->db()->fetchOne("SELECT id FROM wishlists WHERE user_id=? AND course_id=?", [$userId, $courseId]);
        if ($exists) {
            $this->db()->query("DELETE FROM wishlists WHERE user_id=? AND course_id=?", [$userId, $courseId]);
            $this->json(['success' => true, 'action' => 'removed']);
        } else {
            $this->db()->insert('wishlists', ['user_id'=>$userId, 'course_id'=>$courseId, 'created_at'=>date('Y-m-d H:i:s')]);
            $this->json(['success' => true, 'action' => 'added']);
        }
    }

    public function trackLesson(): void
    {
        if (!auth()) { $this->json(['success' => false], 401); return; }
        (new ClassroomController())->completeLesson();
    }

    public function searchCourses(): void
    {
        $q = trim($this->request->input('q', ''));
        if (strlen($q) < 2) { $this->json([]); return; }

        $results = $this->db()->query(
            "SELECT id, title, slug, thumbnail FROM courses WHERE is_published = 1 AND title LIKE ? LIMIT 6",
            ["%$q%"]
        )->fetchAll();

        $this->json($results);
    }
}
