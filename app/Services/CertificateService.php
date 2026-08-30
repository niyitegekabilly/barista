<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\User;
use App\Models\AuditLog;
use App\Core\Database;

class CertificateService {
    public static function generate(int $enrollmentId): ?array {
        $enrollment = Enrollment::find($enrollmentId);
        if (!$enrollment) {
            return null;
        }

        // Check if certificate already exists
        $existing = Certificate::findBy('enrollment_id', $enrollmentId);
        if ($existing) {
            return $existing;
        }

        $course = Course::find((int)$enrollment['course_id']);
        $user = User::find((int)$enrollment['user_id']);
        if (!$course || !$user) {
            return null;
        }

        // Generate unique certificate number: BBA-YYYY-XXXXXX
        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
        $certNumber = "BBA-{$year}-{$random}";

        // Ensure uniqueness
        while (Certificate::findBy('certificate_number', $certNumber)) {
            $random = strtoupper(bin2hex(random_bytes(3)));
            $certNumber = "BBA-{$year}-{$random}";
        }

        $verifyUrl = url("certificate/verify/{$certNumber}");
        // Use a secure SVG QR API or encoded SVG
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($verifyUrl);

        $certId = Certificate::create([
            'certificate_number' => $certNumber,
            'user_id' => (int) $user['id'],
            'course_id' => (int) $course['id'],
            'enrollment_id' => $enrollmentId,
            'student_name' => $user['name'],
            'course_title' => $course['title'],
            'issue_date' => date('Y-m-d'),
            'qr_code_url' => $qrCodeUrl,
            'status' => 'valid'
        ]);

        AuditLog::log('certificate_issued', 'certificate', $certId, [
            'certificate_number' => $certNumber,
            'user_id' => $user['id'],
            'course_id' => $course['id']
        ]);

        return Certificate::find($certId);
    }
}
