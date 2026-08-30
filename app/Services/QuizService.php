<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Core\Database;

class QuizService {
    public static function gradeAttempt(int $quizId, int $userId, array $submittedAnswers): array {
        $quiz = Quiz::findWithQuestions($quizId);
        if (!$quiz) {
            throw new \RuntimeException("Quiz not found.");
        }

        $enrollment = Enrollment::getUserEnrollment($userId, (int)$quiz['course_id']);
        $enrollmentId = $enrollment ? (int)$enrollment['id'] : null;

        // Calculate attempt number
        $prevAttempts = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = :qid AND user_id = :uid",
            ['qid' => $quizId, 'uid' => $userId]
        );
        $attemptNumber = $prevAttempts + 1;

        $totalPoints = 0;
        $earnedPoints = 0;
        $answerRecords = [];

        foreach ($quiz['questions'] as $question) {
            $qId = (int) $question['id'];
            $qPoints = (int) $question['points'];
            $totalPoints += $qPoints;

            $userAnswer = $submittedAnswers[$qId] ?? null;
            $isCorrect = false;
            $pointsAwarded = 0;

            if ($question['question_type'] === 'single_choice' || $question['question_type'] === 'true_false') {
                $correctOpt = Database::fetchOne("SELECT id FROM quiz_options WHERE question_id = :qid AND is_correct = 1 LIMIT 1", ['qid' => $qId]);
                if ($correctOpt && (int)$userAnswer === (int)$correctOpt['id']) {
                    $isCorrect = true;
                    $pointsAwarded = $qPoints;
                    $earnedPoints += $qPoints;
                }
            } elseif ($question['question_type'] === 'fill_blank') {
                $correctOpt = Database::fetchOne("SELECT option_text FROM quiz_options WHERE question_id = :qid AND is_correct = 1 LIMIT 1", ['qid' => $qId]);
                if ($correctOpt && strcasecmp(trim((string)$userAnswer), trim($correctOpt['option_text'])) === 0) {
                    $isCorrect = true;
                    $pointsAwarded = $qPoints;
                    $earnedPoints += $qPoints;
                }
            }

            $answerRecords[] = [
                'question_id' => $qId,
                'selected_option_id' => is_numeric($userAnswer) ? (int)$userAnswer : null,
                'answer_text' => is_string($userAnswer) ? $userAnswer : null,
                'is_correct' => $isCorrect ? 1 : 0,
                'points_awarded' => $pointsAwarded
            ];
        }

        $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0.00;
        $isPassed = ($percentage >= (float)$quiz['passing_score']);

        // Insert attempt in transaction
        $attemptId = 0;
        Database::transaction(function() use (&$attemptId, $quizId, $userId, $enrollmentId, $attemptNumber, $totalPoints, $earnedPoints, $percentage, $isPassed, $answerRecords) {
            $attemptId = Database::insert('quiz_attempts', [
                'quiz_id' => $quizId,
                'user_id' => $userId,
                'enrollment_id' => $enrollmentId,
                'attempt_number' => $attemptNumber,
                'total_points' => $totalPoints,
                'earned_points' => $earnedPoints,
                'score_percentage' => $percentage,
                'is_passed' => $isPassed ? 1 : 0,
                'started_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
                'completed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            foreach ($answerRecords as $rec) {
                $rec['attempt_id'] = $attemptId;
                Database::insert('quiz_answers', $rec);
            }
        });

        // If passed, check if all course criteria met to generate certificate
        if ($isPassed && $enrollmentId) {
            Enrollment::update($enrollmentId, ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')]);
            $cert = CertificateService::generate($enrollmentId);
            
            Notification::send(
                $userId,
                '🎉 Assessment Passed & Certificate Awarded!',
                "Congratulations! You scored {$percentage}% on {$quiz['title']}. Your verified certificate is ready.",
                $cert ? url("student/certificates") : null
            );
        }

        AuditLog::log('quiz_submitted', 'quiz', $quizId, [
            'user_id' => $userId,
            'attempt_id' => $attemptId,
            'score' => $percentage,
            'passed' => $isPassed
        ]);

        return [
            'attempt_id' => $attemptId,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'score_percentage' => $percentage,
            'is_passed' => $isPassed,
            'passing_score' => $quiz['passing_score']
        ];
    }
}
