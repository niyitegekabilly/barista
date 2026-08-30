<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Quiz extends Model {
    protected static string $table = 'quizzes';

    public static function findWithDetails(int $id): ?array {
        $sql = "SELECT q.*, c.title as course_title, c.slug as course_slug, m.title as module_title
                FROM quizzes q
                JOIN courses c ON q.course_id = c.id
                LEFT JOIN modules m ON q.module_id = m.id
                WHERE q.id = :id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function getQuestionsWithOptions(int $quizId, bool $randomize = false): array {
        $orderSql = $randomize ? "ORDER BY RAND()" : "ORDER BY q.sort_order ASC";
        $questions = Database::fetchAll("SELECT * FROM quiz_questions q WHERE q.quiz_id = :qid {$orderSql}", ['qid' => $quizId]);

        foreach ($questions as &$q) {
            $q['options'] = Database::fetchAll(
                "SELECT id, question_id, option_text, is_correct, sort_order
                 FROM quiz_options WHERE question_id = :qid ORDER BY sort_order ASC",
                ['qid' => $q['id']]
            );
        }

        return $questions;
    }
}
