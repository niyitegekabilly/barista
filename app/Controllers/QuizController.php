<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\QuizService;

class QuizController extends Controller {

    /**
     * Display student examination quiz stage.
     */
    public function show(Request $request, int $quizId): void {
        $userId = auth_id();
        if (!$userId) {
            $this->redirect('login?redirect=' . urlencode('student/quiz/' . $quizId));
            return;
        }

        $data = QuizService::getQuizForStudent($quizId, $userId);
        if (!$data['success']) {
            $this->flash('error', $data['message']);
            $this->redirect('student/courses');
            return;
        }

        if (!$data['can_attempt']) {
            $this->flash('warning', 'You have used all available attempts (' . $data['max_attempts'] . ') for this examination.');
            $this->redirect('student/courses');
            return;
        }

        $this->render('student/quiz', [
            'pageTitle'       => 'Assessment Exam: ' . $data['quiz']['title'],
            'quiz'            => $data['quiz'],
            'questions'       => $data['questions'],
            'totalQuestions'  => $data['total_questions'],
            'attempts'        => $data['attempts'],
            'attemptsLeft'    => $data['attempts_left'],
            'maxAttempts'     => $data['max_attempts'],
            'bestScore'       => $data['best_score']
        ], 'dashboard');
    }

    /**
     * Submit and auto-grade quiz examination.
     */
    public function submit(Request $request, int $quizId): void {
        $userId = auth_id();
        if (!$userId) {
            $this->redirect('login');
            return;
        }

        $answers = (array)$request->input('answers', []);
        $durationSeconds = (int)$request->input('duration_seconds', 0);

        $result = QuizService::submitAttempt($userId, $quizId, $answers, $durationSeconds);

        if (!$result['success']) {
            $this->flash('error', $result['message']);
            $this->redirect('student/courses');
            return;
        }

        $this->render('student/quiz-result', [
            'pageTitle'           => 'Exam Results: ' . $result['quiz']['title'],
            'result'              => $result,
            'quiz'                => $result['quiz'],
            'certificateNumber'   => $result['certificate_number']
        ], 'dashboard');
    }
}
