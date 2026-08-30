<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Quiz extends Model {
    protected static string $table = 'quizzes';

    public static function findWithQuestions(int $id): ?array {
        $quiz = self::find($id);
        if (!$quiz) {
            return null;
        }

        $questions = Database::fetchAll("SELECT * FROM quiz_questions WHERE quiz_id = :qid ORDER BY sort_order ASC", ['qid' => $id]);
        foreach ($questions as &$q) {
            $q['options'] = Database::fetchAll("SELECT * FROM quiz_options WHERE question_id = :qid ORDER BY sort_order ASC", ['qid' => $q['id']]);
        }
        $quiz['questions'] = $questions;
        return $quiz;
    }

    public static function getLatestUserAttempt(int $quizId, int $userId): ?array {
        return Database::fetchOne("SELECT * FROM quiz_attempts WHERE quiz_id = :qid AND user_id = :uid ORDER BY id DESC LIMIT 1", [
            'qid' => $quizId,
            'uid' => $userId
        ]);
    }
}
