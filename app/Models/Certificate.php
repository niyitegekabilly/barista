<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Certificate extends Model {
    protected static string $table = 'certificates';

    public static function generateCertificateNumber(): string {
        $prefix = 'BBA-CERT-' . date('Ym') . '-';
        do {
            $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));
            $number = $prefix . $random;
            $exists = Database::fetchOne("SELECT id FROM certificates WHERE certificate_number = :n", ['n' => $number]);
        } while ($exists);

        return $number;
    }

    public static function generatePublicHash(): string {
        return bin2hex(random_bytes(32));
    }

    public static function findByNumber(string $number): ?array {
        return Database::fetchOne("SELECT * FROM certificates WHERE certificate_number = :n LIMIT 1", ['n' => trim($number)]);
    }

    public static function findByHash(string $hash): ?array {
        return Database::fetchOne("SELECT * FROM certificates WHERE public_hash = :h LIMIT 1", ['h' => trim($hash)]);
    }

    public static function findWithDetails(int $id): ?array {
        $sql = "SELECT c.*, u.name as student_name, u.email as student_email, u.student_id,
                       co.title as course_title, co.slug as course_slug, co.level as course_level,
                       inst.name as course_instructor_name,
                       e.progress_percent, e.completed_at as enrollment_completed_at
                FROM certificates c
                JOIN users u ON c.user_id = u.id
                JOIN courses co ON c.course_id = co.id
                JOIN enrollments e ON c.enrollment_id = e.id
                LEFT JOIN users inst ON co.created_by = inst.id
                WHERE c.id = :id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function calculateGradeLetter(float $score): string {
        if ($score >= 95) return 'Distinction (A+)';
        if ($score >= 85) return 'Honors (A)';
        if ($score >= 75) return 'Pass (B)';
        if ($score >= 60) return 'Pass (C)';
        return 'Needs Review';
    }
}
