<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Certificate extends Model {
    protected static string $table = 'certificates';

    public static function findByNumber(string $certificateNumber): ?array {
        $sql = "SELECT cert.*, u.email as student_email, c.slug as course_slug, c.level as course_level, c.duration_hours,
                       cat.name as category_name, inst.name as instructor_name
                FROM certificates cert
                JOIN users u ON cert.user_id = u.id
                JOIN courses c ON cert.course_id = c.id
                JOIN categories cat ON c.category_id = cat.id
                JOIN users inst ON c.created_by = inst.id
                WHERE cert.certificate_number = :num
                LIMIT 1";
        return Database::fetchOne($sql, ['num' => $certificateNumber]);
    }

    public static function getUserCertificates(int $userId): array {
        $sql = "SELECT cert.*, c.slug as course_slug, c.thumbnail, cat.name as category_name
                FROM certificates cert
                JOIN courses c ON cert.course_id = c.id
                JOIN categories cat ON c.category_id = cat.id
                WHERE cert.user_id = :uid AND cert.status = 'valid'
                ORDER BY cert.issue_date DESC";
        return Database::fetchAll($sql, ['uid' => $userId]);
    }
}
