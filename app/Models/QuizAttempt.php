<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class QuizAttempt extends Model {
    protected static string $table = 'quiz_attempts';

    public static function getUserAttempts(int $quizId, int $userId): array {
        return Database::fetchAll(
            "SELECT * FROM quiz_attempts WHERE quiz_id = :qid AND user_id = :uid ORDER BY attempt_number DESC",
            ['qid' => $quizId, 'uid' => $userId]
        );
    }

    public static function getBestScore(int $quizId, int $userId): ?float {
        $val = Database::fetchValue(
            "SELECT MAX(score_percentage) FROM quiz_attempts WHERE quiz_id = :qid AND user_id = :uid",
            ['qid' => $quizId, 'uid' => $userId]
        );
        return $val !== null ? (float)$val : null;
    }
}
