<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\ClassroomService;
use App\Models\LessonResource;

class ClassroomController extends Controller {

    /**
     * Display the 360° Student Classroom Stage.
     */
    public function show(Request $request, string $courseSlug, int $lessonId = 0): void {
        $userId = auth_id();
        if (!$userId) {
            $this->redirect('login?redirect=' . urlencode('student/classroom/' . $courseSlug));
            return;
        }

        // Support query param ?lesson=ID as well
        if (!$lessonId && $request->input('lesson')) {
            $lessonId = (int)$request->input('lesson');
        }

        $data = ClassroomService::getClassroomData($userId, $courseSlug, $lessonId);

        if (!$data['success']) {
            if ($data['code'] === 403) {
                $this->flash('warning', $data['message']);
                $this->redirect('courses/' . $courseSlug);
                return;
            }
            $this->abort(404);
            return;
        }

        $this->render('student/classroom', [
            'pageTitle'           => $data['current_lesson']['title'] ?? $data['course']['title'],
            'course'              => $data['course'],
            'enrollment'          => $data['enrollment'],
            'modules'             => $data['modules'],
            'allLessons'          => $data['all_lessons'],
            'currentLesson'       => $data['current_lesson'],
            'prevLesson'          => $data['prev_lesson'],
            'nextLesson'          => $data['next_lesson'],
            'completedLessonIds'  => $data['completed_lesson_ids'],
            'progressPercent'     => $data['progress_percent'],
            'resources'           => $data['resources'],
            'notes'               => $data['notes'],
            'discussions'         => $data['discussions'],
            'bookmarks'           => $data['bookmarks'],
            'lessonProgress'      => $data['lesson_progress']
        ], 'dashboard');
    }

    /**
     * AJAX Heartbeat: Save video playback position & active time spent.
     */
    public function saveProgress(Request $request): void {
        $userId = auth_id();
        $enrollmentId = (int)$request->input('enrollment_id');
        $lessonId = (int)$request->input('lesson_id');
        $positionSeconds = (int)$request->input('position_seconds', 0);
        $timeSpent = (int)$request->input('time_spent', 0);
        $markComplete = (bool)$request->input('mark_complete', 0);
        $speed = (float)$request->input('speed', 1.0);

        $res = ClassroomService::saveProgress($userId, $enrollmentId, $lessonId, $positionSeconds, $timeSpent, $markComplete, $speed);
        Response::json($res);
    }

    /**
     * Complete Lesson action.
     */
    public function completeLesson(Request $request): void {
        $userId = auth_id();
        $enrollmentId = (int)$request->input('enrollment_id');
        $lessonId = (int)$request->input('lesson_id');

        $res = ClassroomService::saveProgress($userId, $enrollmentId, $lessonId, 0, 0, true);
        Response::json($res);
    }

    /**
     * Save private student note.
     */
    public function addNote(Request $request): void {
        $userId = auth_id();
        $courseId = (int)$request->input('course_id');
        $lessonId = (int)$request->input('lesson_id');
        $noteText = (string)$request->input('note_text', '');
        $timestamp = (int)$request->input('timestamp_seconds', 0);

        $res = ClassroomService::saveNote($userId, $courseId, $lessonId, $noteText, $timestamp);
        Response::json($res);
    }

    /**
     * Delete student note.
     */
    public function deleteNote(Request $request, int $id): void {
        $userId = auth_id();
        $success = ClassroomService::deleteNote($userId, $id);
        Response::json(['success' => $success]);
    }

    /**
     * Post a question or reply to lesson discussion.
     */
    public function postDiscussion(Request $request): void {
        $userId = auth_id();
        $courseId = (int)$request->input('course_id');
        $lessonId = (int)$request->input('lesson_id');
        $question = (string)$request->input('question', '');
        $parentId = $request->input('parent_id') ? (int)$request->input('parent_id') : null;

        $res = ClassroomService::postDiscussion($userId, $courseId, $lessonId, $question, $parentId);
        Response::json($res);
    }

    /**
     * Toggle saved video bookmark.
     */
    public function toggleBookmark(Request $request): void {
        $userId = auth_id();
        $lessonId = (int)$request->input('lesson_id');
        $title = (string)$request->input('title', '');
        $timestamp = (int)$request->input('timestamp_seconds', 0);

        $res = ClassroomService::toggleBookmark($userId, $lessonId, $title, $timestamp);
        Response::json($res);
    }

    /**
     * Download Lesson Resource Attachment safely.
     */
    public function downloadResource(Request $request, int $id): void {
        $userId = auth_id();
        $resource = Database::fetchOne("SELECT * FROM lesson_resources WHERE id = :id LIMIT 1", ['id' => $id]);
        if (!$resource) {
            $this->abort(404);
            return;
        }

        // Increment download counter
        LessonResource::incrementDownload($id);

        if (!empty($resource['is_external']) && !empty($resource['external_url'])) {
            $this->redirect($resource['external_url']);
            return;
        }

        $filePath = BASE_PATH . '/public/uploads/' . $resource['file_path'];
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($resource['file_path']) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }

        $this->flash('warning', 'Resource file not found on server.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? 'student/courses');
    }
}
