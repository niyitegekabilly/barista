<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class LessonResource extends Model {
    protected static string $table = 'lesson_resources';

    public static function formatFileSize(?int $bytes): string {
        if (!$bytes || $bytes <= 0) return 'File Attachment';
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        return round($bytes / 1024, 0) . ' KB';
    }

    public static function incrementDownload(int $id): void {
        Database::query("UPDATE lesson_resources SET download_count = download_count + 1 WHERE id = :id", ['id' => $id]);
    }
}
