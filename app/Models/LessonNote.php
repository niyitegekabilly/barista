<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class LessonNote extends Model {
    protected static string $table = 'lesson_notes';

    public static function formatTimestamp(int $seconds): string {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}
