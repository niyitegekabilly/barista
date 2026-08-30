<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\AuditLog;

class CertificateService {

    /**
     * Automatically generate certificate for a completed enrollment.
     */
    public static function generateCertificateForEnrollment(int $enrollmentId, string $templateType = 'specialty_barista'): array {
        $enrollment = Database::fetchOne(
            "SELECT e.*, u.name as user_name, u.email as user_email, u.student_id,
                    c.title as course_title, c.id as course_id, inst.name as instructor_name
             FROM enrollments e
             JOIN users u ON e.user_id = u.id
             JOIN courses c ON e.course_id = c.id
             LEFT JOIN users inst ON c.created_by = inst.id
             WHERE e.id = :id LIMIT 1",
            ['id' => $enrollmentId]
        );

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment record not found.'];
        }

        // Check if certificate already exists for this enrollment
        $existing = Database::fetchOne("SELECT * FROM certificates WHERE enrollment_id = :eid LIMIT 1", ['eid' => $enrollmentId]);
        if ($existing) {
            return [
                'success'            => true,
                'certificate'        => $existing,
                'certificate_number' => $existing['certificate_number'],
                'is_new'             => false
            ];
        }

        // Calculate average grade score from quizzes attached to this course
        $avgScore = Database::fetchValue(
            "SELECT AVG(qa.score_percentage)
             FROM quiz_attempts qa
             JOIN quizzes q ON qa.quiz_id = q.id
             WHERE q.course_id = :cid AND qa.user_id = :uid AND qa.is_passed = 1",
            ['cid' => $enrollment['course_id'], 'uid' => $enrollment['user_id']]
        );

        $gradeScore = ($avgScore !== null && $avgScore > 0) ? round((float)$avgScore, 2) : 100.00;
        $gradeLetter = Certificate::calculateGradeLetter($gradeScore);

        $certNumber = Certificate::generateCertificateNumber();
        $publicHash = Certificate::generatePublicHash();
        $today = date('Y-m-d');
        $verifyUrl = url('certificate/verify/' . $certNumber);

        $certId = Database::insert('certificates', [
            'certificate_number' => $certNumber,
            'user_id'            => $enrollment['user_id'],
            'course_id'          => $enrollment['course_id'],
            'enrollment_id'      => $enrollmentId,
            'student_name'       => $enrollment['user_name'],
            'course_title'       => $enrollment['course_title'],
            'template_type'      => $templateType,
            'instructor_name'    => $enrollment['instructor_name'] ?? 'Beyond Barista Master Trainers',
            'grade_score'        => $gradeScore,
            'grade_letter'       => $gradeLetter,
            'public_hash'        => $publicHash,
            'issue_date'         => $today,
            'qr_code_url'        => $verifyUrl,
            'status'             => 'valid',
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s')
        ]);

        // Send Student Notification
        Notification::send(
            (int)$enrollment['user_id'],
            'Official Certificate Issued: ' . $enrollment['course_title'],
            "Congratulations! Your official digital certificate {$certNumber} has been verified and issued. View or download your certificate.",
            url('student/certificates/' . $certNumber)
        );

        $cert = Database::fetchOne("SELECT * FROM certificates WHERE id = :id", ['id' => $certId]);

        return [
            'success'            => true,
            'certificate'        => $cert,
            'certificate_number' => $certNumber,
            'is_new'             => true
        ];
    }

    /**
     * Verify certificate by serial number or public hash and record verification audit log.
     */
    public static function verifyCertificate(string $codeOrHash, ?string $ip = null, ?string $ua = null): array {
        $clean = trim($codeOrHash);
        if (empty($clean)) {
            return ['valid' => false, 'message' => 'Please enter a certificate verification code.'];
        }

        $cert = Database::fetchOne(
            "SELECT c.*, u.name as student_name, u.student_id,
                    co.title as course_title, co.slug as course_slug, co.level as course_level,
                    inst.name as instructor_name
             FROM certificates c
             JOIN users u ON c.user_id = u.id
             JOIN courses co ON c.course_id = co.id
             LEFT JOIN users inst ON co.created_by = inst.id
             WHERE c.certificate_number = :c1 OR c.public_hash = :c2 LIMIT 1",
            ['c1' => $clean, 'c2' => $clean]
        );

        if (!$cert) {
            return [
                'valid'   => false,
                'code'    => $clean,
                'message' => 'No certificate found matching code "' . htmlspecialchars($clean) . '". Please verify the certificate serial number.'
            ];
        }

        // Log Verification Audit Entry
        Database::insert('certificate_verifications', [
            'certificate_id'     => $cert['id'],
            'certificate_number' => $cert['certificate_number'],
            'ip_address'         => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'user_agent'         => $ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'Web Verification'),
            'verified_at'        => date('Y-m-d H:i:s')
        ]);

        $isValid = ($cert['status'] === 'valid');

        // Generate LinkedIn Share URL
        $linkedInUrl = "https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME" .
            "&name=" . urlencode($cert['course_title']) .
            "&organizationName=" . urlencode('Beyond Barista Academy') .
            "&issueYear=" . date('Y', strtotime($cert['issue_date'])) .
            "&issueMonth=" . date('n', strtotime($cert['issue_date'])) .
            "&certUrl=" . urlencode(url('certificate/verify/' . $cert['certificate_number'])) .
            "&certId=" . urlencode($cert['certificate_number']);

        return [
            'valid'          => $isValid,
            'certificate'    => $cert,
            'status'         => $cert['status'],
            'status_label'   => ucfirst($cert['status']),
            'linkedInUrl'    => $linkedInUrl,
            'message'        => $isValid ? 'Certificate is authentic and officially verified.' : 'Certificate has been ' . $cert['status'] . '.'
        ];
    }

    /**
     * Revoke certificate.
     */
    public static function revokeCertificate(int $certId, string $reason): array {
        $cert = Database::fetchOne("SELECT * FROM certificates WHERE id = :id", ['id' => $certId]);
        if (!$cert) {
            return ['success' => false, 'message' => 'Certificate not found.'];
        }

        Database::update('certificates', [
            'status'            => 'revoked',
            'revocation_reason' => trim($reason) ?: 'Administrative revocation',
            'revoked_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ], ['id' => $certId]);

        AuditLog::log('certificate.revoked', 'certificates', $certId, [
            'certificate_number' => $cert['certificate_number'],
            'reason'             => $reason
        ]);

        return ['success' => true, 'message' => 'Certificate ' . $cert['certificate_number'] . ' has been revoked.'];
    }

    /**
     * Reissue / restore valid certificate.
     */
    public static function reissueCertificate(int $certId): array {
        $cert = Database::fetchOne("SELECT * FROM certificates WHERE id = :id", ['id' => $certId]);
        if (!$cert) {
            return ['success' => false, 'message' => 'Certificate not found.'];
        }

        Database::update('certificates', [
            'status'            => 'valid',
            'revocation_reason' => null,
            'revoked_at'        => null,
            'updated_at'        => date('Y-m-d H:i:s')
        ], ['id' => $certId]);

        return ['success' => true, 'message' => 'Certificate ' . $cert['certificate_number'] . ' restored to valid status.'];
    }

    /**
     * Issue manual certificate from admin workspace.
     */
    public static function issueManualCertificate(int $userId, int $courseId, ?string $studentName = null, ?float $gradeScore = null): array {
        $user = Database::fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
        $course = Database::fetchOne("SELECT * FROM courses WHERE id = :id", ['id' => $courseId]);

        if (!$user || !$course) {
            return ['success' => false, 'message' => 'Invalid user or course selected.'];
        }

        // Find or create enrollment
        $enrollment = Database::fetchOne(
            "SELECT * FROM enrollments WHERE user_id = :uid AND course_id = :cid",
            ['uid' => $userId, 'cid' => $courseId]
        );

        $enrollId = $enrollment ? (int)$enrollment['id'] : Database::insert('enrollments', [
            'user_id'          => $userId,
            'course_id'        => $courseId,
            'status'           => 'completed',
            'progress_percent' => 100,
            'enrolled_at'      => date('Y-m-d H:i:s'),
            'completed_at'     => date('Y-m-d H:i:s')
        ]);

        return static::generateCertificateForEnrollment($enrollId);
    }

    /**
     * Get Executive KPI Metrics.
     */
    public static function getCertificateKpis(): array {
        $totalIssued = (int)(Database::fetchValue("SELECT COUNT(*) FROM certificates") ?: 0);
        $totalValid = (int)(Database::fetchValue("SELECT COUNT(*) FROM certificates WHERE status = 'valid'") ?: 0);
        $totalRevoked = (int)(Database::fetchValue("SELECT COUNT(*) FROM certificates WHERE status = 'revoked'") ?: 0);
        $totalVerifications = (int)(Database::fetchValue("SELECT COUNT(*) FROM certificate_verifications") ?: 0);
        $recentVerifications = (int)(Database::fetchValue("SELECT COUNT(*) FROM certificate_verifications WHERE verified_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)") ?: 0);

        return [
            'total_issued'         => $totalIssued,
            'total_valid'          => $totalValid,
            'total_revoked'        => $totalRevoked,
            'total_verifications'  => $totalVerifications,
            'recent_verifications' => $recentVerifications
        ];
    }

    /**
     * Export certificates as CSV.
     */
    public static function exportCertificatesCsv(): string {
        $certs = Database::fetchAll(
            "SELECT c.certificate_number, c.student_name, u.email as student_email,
                    c.course_title, c.issue_date, c.grade_score, c.grade_letter, c.status,
                    (SELECT COUNT(*) FROM certificate_verifications WHERE certificate_id = c.id) as verifications_count
             FROM certificates c
             JOIN users u ON c.user_id = u.id
             ORDER BY c.issue_date DESC"
        );

        $out = fopen('php://temp', 'w');
        fputcsv($out, ['Certificate #', 'Student Name', 'Email', 'Course Title', 'Issue Date', 'Score %', 'Grade', 'Status', 'Verifications']);

        foreach ($certs as $c) {
            fputcsv($out, [
                $c['certificate_number'],
                $c['student_name'],
                $c['student_email'],
                $c['course_title'],
                $c['issue_date'],
                $c['grade_score'] . '%',
                $c['grade_letter'],
                ucfirst($c['status']),
                $c['verifications_count']
            ]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv;
    }
}
