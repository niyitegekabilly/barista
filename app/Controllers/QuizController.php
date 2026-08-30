<?php

namespace App\Controllers;

use App\Core\Controller;

class QuizController extends Controller
{
    public function show(\App\Core\Request $request, int $quizId): void
    {
        $userId = auth()['id'];

        // Get quiz with questions and options
        $quiz = $this->db()->fetchOne("SELECT * FROM quizzes WHERE id = ?", [$quizId]);
        if (!$quiz) {
            $this->abort(404);
            return;
        }

        // Get questions
        $questions = $this->db()->query(
            "SELECT q.*, GROUP_CONCAT(
                JSON_OBJECT('id', o.id, 'text', o.option_text)
            ) options FROM quiz_questions q
             LEFT JOIN quiz_options o ON o.question_id = q.id
             WHERE q.quiz_id = ? GROUP BY q.id ORDER BY q.sort_order",
            [$quizId]
        )->fetchAll();

        // Decode and process options
        foreach ($questions as &$q) {
            if ($q['options']) {
                $q['options'] = json_decode('[' . $q['options'] . ']', true);
            } else {
                $q['options'] = [];
            }
        }

        $quiz['questions'] = $questions;

        // Check attempt limits
        $attempts = $this->db()->fetchOne(
            "SELECT COUNT(*) cnt FROM quiz_attempts WHERE quiz_id = ? AND user_id = ?",
            [$quizId, $userId]
        )['cnt'] ?? 0;

        if ($attempts >= ($quiz['max_attempts'] ?? 3)) {
            $this->flash('error', 'You have used all available attempts for this quiz.');
            $this->redirect('/student/courses');
            return;
        }

        $this->render('student/quiz', compact('quiz'), 'dashboard');
    }

    public function submit(Request $request, int $quizId): void
    {
        $userId  = auth()['id'];
        $answers = $this->request->input('answers', []);

        $quiz = $this->db()->fetchOne("SELECT * FROM quizzes WHERE id = ?", [$quizId]);
        if (!$quiz) {
            $this->abort(404);
            return;
        }

        // Get questions and calculate score
        $questions = $this->db()->query(
            "SELECT * FROM quiz_questions WHERE quiz_id = ?",
            [$quizId]
        )->fetchAll();

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($questions as $q) {
            $totalPoints += $q['points'];
            $userAnswer = $answers[$q['id']] ?? null;

            if ($q['question_type'] === 'single_choice' || $q['question_type'] === 'true_false') {
                $correctOpt = $this->db()->fetchOne(
                    "SELECT id FROM quiz_options WHERE question_id = ? AND is_correct = 1",
                    [$q['id']]
                );
                if ($correctOpt && (int)$userAnswer === (int)$correctOpt['id']) {
                    $earnedPoints += $q['points'];
                }
            }
        }

        $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $isPassed = $percentage >= $quiz['passing_score'];

        // Record attempt
        $this->db()->insert('quiz_attempts', [
            'quiz_id' => $quizId,
            'user_id' => $userId,
            'attempt_number' => 1,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'score_percentage' => $percentage,
            'is_passed' => $isPassed ? 1 : 0,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        $result = [
            'quiz' => $quiz,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'percentage' => $percentage,
            'is_passed' => $isPassed,
        ];

        $this->render('student/quiz-result', compact('result'), 'dashboard');
    }
}
