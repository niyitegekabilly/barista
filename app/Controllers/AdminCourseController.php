<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Category;
use App\Models\User;
use App\Services\CourseService;
use App\Services\CourseApprovalService;

/**
 * Courses & Approval — real lifecycle/approval workflow, KPI dashboard,
 * search/filter/sort/pagination, and bulk actions. Replaces the old
 * AdminController::courses()/approveCourse()/rejectCourse()/unpublishCourse()
 * (which only toggled `is_published`).
 */
class AdminCourseController extends Controller
{
    public function index(Request $request): void
    {
        $filters = [
            'q' => $request->input('q', ''),
            'status' => $request->input('status', ''),
            'category_id' => $request->input('category_id', ''),
            'instructor_id' => $request->input('instructor_id', ''),
            'is_free' => $request->input('is_free', ''),
            'sort' => $request->input('sort', 'updated_at'),
            'dir' => $request->input('dir', 'DESC'),
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 20),
        ];

        $kpis = CourseService::getKpiStats();
        $result = CourseService::queryCourses($filters);
        $categories = Category::all('name ASC');
        $instructors = $this->db()->fetchAll(
            "SELECT DISTINCT u.id, u.name FROM users u JOIN courses c ON c.created_by = u.id ORDER BY u.name ASC"
        );

        $this->render('admin/courses/index', [
            'pageTitle' => 'Courses & Approval',
            'courses' => $result['data'],
            'pagination' => $result['pagination'],
            'kpis' => $kpis,
            'filters' => $filters,
            'categories' => $categories,
            'instructors' => $instructors,
            'canReview' => User::hasPermission((int)auth_id(), 'courses.review'),
            'canPublish' => User::hasPermission((int)auth_id(), 'courses.publish'),
            'canArchive' => User::hasPermission((int)auth_id(), 'courses.archive'),
        ], 'dashboard');
    }

    public function show(Request $request, int $id): void
    {
        $course = CourseService::getCourseDetail($id);
        if (!$course) {
            $this->flash('danger', 'Course not found.');
            $this->redirect('admin/courses');
            return;
        }

        $this->render('admin/courses/show', [
            'pageTitle' => $course['title'],
            'course' => $course,
            'canReview' => User::hasPermission((int)auth_id(), 'courses.review'),
            'canPublish' => User::hasPermission((int)auth_id(), 'courses.publish'),
            'canArchive' => User::hasPermission((int)auth_id(), 'courses.archive'),
        ], 'dashboard');
    }

    public function startReview(Request $request, int $id): void
    {
        $this->runTransition(fn (CourseApprovalService $s) => $s->startReview($id, (int)auth_id()), $id, 'Review started.');
    }

    public function approve(Request $request, int $id): void
    {
        $comment = trim((string)$request->input('comment', ''));
        $this->runTransition(fn (CourseApprovalService $s) => $s->approve($id, (int)auth_id(), $comment ?: null), $id, 'Course approved.');
    }

    public function reject(Request $request, int $id): void
    {
        $comment = trim((string)$request->input('comment', ''));
        if ($comment === '') {
            $this->flash('danger', 'A comment is required to reject a course.');
            $this->redirect('admin/courses/' . $id);
            return;
        }
        $this->runTransition(fn (CourseApprovalService $s) => $s->reject($id, (int)auth_id(), $comment), $id, 'Course rejected.');
    }

    public function requestChanges(Request $request, int $id): void
    {
        $comment = trim((string)$request->input('comment', ''));
        if ($comment === '') {
            $this->flash('danger', 'A comment is required to request changes.');
            $this->redirect('admin/courses/' . $id);
            return;
        }
        $this->runTransition(fn (CourseApprovalService $s) => $s->requestChanges($id, (int)auth_id(), $comment), $id, 'Changes requested.');
    }

    public function publish(Request $request, int $id): void
    {
        $this->runTransition(fn (CourseApprovalService $s) => $s->publish($id, (int)auth_id()), $id, 'Course published.');
    }

    public function schedule(Request $request, int $id): void
    {
        $publishAt = (string)$request->input('scheduled_publish_at', '');
        $this->runTransition(fn (CourseApprovalService $s) => $s->schedule($id, (int)auth_id(), $publishAt), $id, 'Course scheduled.');
    }

    public function unpublish(Request $request, int $id): void
    {
        $this->runTransition(fn (CourseApprovalService $s) => $s->unpublish($id, (int)auth_id()), $id, 'Course unpublished.');
    }

    public function archive(Request $request, int $id): void
    {
        $this->runTransition(fn (CourseApprovalService $s) => $s->archive($id, (int)auth_id()), $id, 'Course archived.');
    }

    public function restore(Request $request, int $id): void
    {
        $this->runTransition(fn (CourseApprovalService $s) => $s->restore($id, (int)auth_id()), $id, 'Course restored to draft.');
    }

    public function bulkAction(Request $request): void
    {
        $action = (string)$request->input('bulk_action', '');
        $courseIds = $request->input('course_ids', []);
        if (is_string($courseIds)) {
            $courseIds = explode(',', $courseIds);
        }

        $res = CourseService::executeBulkAction($action, $courseIds, (int)auth_id());

        if ($request->isAjax()) {
            $this->json($res, $res['success'] ? 200 : 400);
            return;
        }

        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/courses');
    }

    /**
     * Shared plumbing for every single-course transition endpoint: run the
     * service call (which already handles permission checks/history/audit
     * logging/notifications), flash a contextual success/error message, and
     * redirect back to the course detail page.
     */
    private function runTransition(callable $action, int $courseId, string $successMessage): void
    {
        $service = new CourseApprovalService();
        $success = true;
        $message = $successMessage;

        try {
            $action($service);
            $this->flash('success', $successMessage);
        } catch (\Throwable $e) {
            $success = false;
            $message = $e->getMessage();
            $this->flash('danger', $message);
        }

        if ($this->request->isAjax()) {
            $this->json(['success' => $success, 'message' => $message]);
            return;
        }

        $this->redirect('admin/courses/' . $courseId);
    }
}
