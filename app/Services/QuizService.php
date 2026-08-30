<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Notification;

class QuizService {

    /**
     * Load quiz with questions and student attempt status.
     */
    public static function getQuizForStudent(int $quizId, int $userId): array {
        $quiz = Quiz::findWithDetails($quizId);
        if (!$quiz) {
            return ['success' => false, 'code' => 404, 'message' => 'Quiz not found.'];
        }

        $attempts = QuizAttempt::getUserAttempts($quizId, $userId);
        $maxAttempts = (int)($quiz['max_attempts'] ?? 3);
        $attemptsCount = count($attempts);
        $canAttempt = ($attemptsCount < $maxAttempts);
        $bestScore = QuizAttempt::getBestScore($quizId, $userId);

        $questions = Quiz::getQuestionsWithOptions($quizId, (bool)($quiz['randomize_questions'] ?? false));

        return [
            'success'          => true,
            'quiz'             => $quiz,
            'questions'        => $questions,
            'total_questions'  => count($questions),
            'attempts'         => $attempts,
            'attempts_count'   => $attemptsCount,
            'max_attempts'     => $maxAttempts,
            'attempts_left'    => max(0, $maxAttempts - $attemptsCount),
            'can_attempt'      => $canAttempt,
            'best_score'       => $bestScore
        ];
    }

    /**
     * Submit and auto-grade quiz attempt.
     */
    public static function submitAttempt(int $userId, int $quizId, array $answers, int $durationSeconds = 0): array {
        $quiz = Database::fetchOne("SELECT * FROM quizzes WHERE id = :id", ['id' => $quizId]);
        if (!$quiz) {
            return ['success' => false, 'message' => 'Quiz not found.'];
        }

        $courseId = (int)$quiz['course_id'];
        $attemptsCount = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = :qid AND user_id = :uid",
            ['qid' => $quizId, 'uid' => $userId]
        ) ?: 0);

        if ($attemptsCount >= (int)($quiz['max_attempts'] ?? 3)) {
            return ['success' => false, 'message' => 'You have exhausted all available attempts for this examination.'];
        }

        // Fetch Enrollment
        $enrollment = Database::fetchOne(
            "SELECT * FROM enrollments WHERE course_id = :cid AND user_id = :uid",
            ['cid' => $courseId, 'uid' => $userId]
        );
        $enrollmentId = $enrollment ? (int)$enrollment['id'] : null;

        // Fetch All Questions
        $questions = Database::fetchAll("SELECT * FROM quiz_questions WHERE quiz_id = :qid ORDER BY sort_order ASC", ['qid' => $quizId]);

        $totalPoints = 0;
        $earnedPoints = 0;
        $gradedAnswers = [];
        $hasEssay = false;

        foreach ($questions as $q) {
            $qId = (int)$q['id'];
            $points = (int)($q['points'] ?? 1);
            $totalPoints += $points;
            $userAns = $answers[$qId] ?? null;

            $isCorrect = 0;
            $pointsAwarded = 0;
            $selectedOptId = null;
            $answerText = null;

            if ($q['question_type'] === 'single_choice' || $q['question_type'] === 'true_false') {
                $selectedOptId = (int)$userAns;
                $correctOpt = Database::fetchOne("SELECT id FROM quiz_options WHERE question_id = :qid AND is_correct = 1 LIMIT 1", ['qid' => $qId]);
                if ($correctOpt && $selectedOptId === (int)$correctOpt['id']) {
                    $isCorrect = 1;
                    $pointsAwarded = $points;
                }
            } elseif ($q['question_type'] === 'multiple_choice') {
                // Array of selected options
                $selectedIds = is_array($userAns) ? array_map('intval', $userAns) : [(int)$userAns];
                $correctOptIds = array_column(
                    Database::fetchAll("SELECT id FROM quiz_options WHERE question_id = :qid AND is_correct = 1", ['qid' => $qId]),
                    'id'
                );
                sort($selectedIds);
                sort($correctOptIds);
                if ($selectedIds === $correctOptIds) {
                    $isCorrect = 1;
                    $pointsAwarded = $points;
                }
                $answerText = json_encode($selectedIds);
            } elseif ($q['question_type'] === 'essay' || $q['question_type'] === 'short_answer') {
                $answerText = is_string($userAns) ? trim($userAns) : '';
                $hasEssay = true;
            }

            $earnedPoints += $pointsAwarded;

            $gradedAnswers[] = [
                'question_id'       => $qId,
                'question_text'     => $q['question_text'],
                'question_type'     => $q['question_type'],
                'explanation'       => $q['explanation'],
                'selected_option_id'=> $selectedOptId,
                'answer_text'       => $answerText,
                'is_correct'        => $isCorrect,
                'points_awarded'    => $pointsAwarded,
                'max_points'        => $points
            ];
        }

        $percentage = ($totalPoints > 0) ? round(($earnedPoints / $totalPoints) * 100, 2) : 0.00;
        $isPassed = ($percentage >= (float)$quiz['passing_score']);
        $status = $hasEssay ? 'submitted' : ($isPassed ? 'passed' : 'failed');

        // Record Attempt
        $attemptId = Database::insert('quiz_attempts', [
            'quiz_id'          => $quizId,
            'user_id'          => $userId,
            'enrollment_id'    => $enrollmentId,
            'attempt_number'   => $attemptsCount + 1,
            'total_points'     => $totalPoints,
            'earned_points'    => $earnedPoints,
            'score_percentage' => $percentage,
            'duration_seconds' => $durationSeconds,
            'is_passed'        => $isPassed ? 1 : 0,
            'status'           => $status,
            'started_at'       => date('Y-m-d H:i:s', time() - max(1, $durationSeconds)),
            'completed_at'     => date('Y-m-d H:i:s'),
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        // Record Question Answers
        foreach ($gradedAnswers as $ga) {
            Database::insert('quiz_answers', [
                'attempt_id'        => $attemptId,
                'question_id'       => $ga['question_id'],
                'selected_option_id'=> $ga['selected_option_id'],
                'answer_text'       => $ga['answer_text'],
                'is_correct'        => $ga['is_correct'],
                'points_awarded'    => $ga['points_awarded']
            ]);
        }

        // Automated Certificate Trigger:
        // If passed AND course enrollment has reached 100% progress, issue certificate!
        $certificateGenerated = null;
        if ($isPassed && $enrollmentId) {
            $progressPct = (int)($enrollment['progress_percent'] ?? 0);
            if ($progressPct >= 100) {
                $certRes = CertificateService::generateCertificateForEnrollment($enrollmentId);
                if ($certRes['success']) {
                    $certificateGenerated = $certRes['certificate_number'];
                }
            }
        }

        return [
            'success'              => true,
            'attempt_id'           => $attemptId,
            'quiz'                 => $quiz,
            'total_points'         => $totalPoints,
            'earned_points'        => $earnedPoints,
            'percentage'           => $percentage,
            'passing_score'        => (float)$quiz['passing_score'],
            'is_passed'            => $isPassed,
            'graded_answers'       => $gradedAnswers,
            'certificate_number'   => $certificateGenerated
        ];
    }
}
