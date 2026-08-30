<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Core/Session.php';
require BASE_PATH . '/app/Helpers/helpers.php';
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Core/Model.php';
require BASE_PATH . '/app/Models/Order.php';
require BASE_PATH . '/app/Models/Payment.php';
require BASE_PATH . '/app/Models/Course.php';
require BASE_PATH . '/app/Models/Coupon.php';
require BASE_PATH . '/app/Models/CouponCampaign.php';
require BASE_PATH . '/app/Models/CouponRedemption.php';
require BASE_PATH . '/app/Models/MembershipPlan.php';
require BASE_PATH . '/app/Models/Membership.php';
require BASE_PATH . '/app/Models/MembershipRenewal.php';
require BASE_PATH . '/app/Models/LessonNote.php';
require BASE_PATH . '/app/Models/LessonDiscussion.php';
require BASE_PATH . '/app/Models/LessonResource.php';
require BASE_PATH . '/app/Models/Certificate.php';
require BASE_PATH . '/app/Models/Quiz.php';
require BASE_PATH . '/app/Models/QuizAttempt.php';
require BASE_PATH . '/app/Models/Invoice.php';
require BASE_PATH . '/app/Models/Receipt.php';
require BASE_PATH . '/app/Models/Refund.php';
require BASE_PATH . '/app/Models/FinancialTransaction.php';
require BASE_PATH . '/app/Models/AuditLog.php';
require BASE_PATH . '/app/Models/Notification.php';
require BASE_PATH . '/app/Services/VideoService.php';
require BASE_PATH . '/app/Services/ClassroomService.php';
require BASE_PATH . '/app/Services/CertificateService.php';
require BASE_PATH . '/app/Services/QuizService.php';
require BASE_PATH . '/app/Services/MembershipService.php';
require BASE_PATH . '/app/Services/CouponService.php';
require BASE_PATH . '/app/Services/OrderService.php';
require BASE_PATH . '/app/Services/FinanceService.php';

echo "=========================================================\n";
echo "Beyond Barista Academy — Certificates & Quizzes Test Suite\n";
echo "=========================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo "❌ [FAIL] {$name} - {$details}\n";
        $failed++;
    }
}

// 0. Setup test user & category & course
$testUser = \App\Core\Database::fetchOne("SELECT id FROM users LIMIT 1");
$userId = $testUser ? (int)$testUser['id'] : 1;

$testCat = \App\Core\Database::fetchOne("SELECT id FROM categories LIMIT 1");
$categoryId = $testCat ? (int)$testCat['id'] : 1;

$courseId = \App\Core\Database::insert('courses', [
    'title' => 'Sensory Science & Cupping ' . time(),
    'slug' => 'sensory-science-' . time(),
    'category_id' => $categoryId,
    'price' => 85000.00,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

$enrollmentId = \App\Core\Database::insert('enrollments', [
    'user_id' => $userId,
    'course_id' => $courseId,
    'status' => 'completed',
    'progress_percent' => 100,
    'enrolled_at' => date('Y-m-d H:i:s'),
    'completed_at' => date('Y-m-d H:i:s')
]);

// 1. Create Quiz with 3 Questions
$quizId = \App\Core\Database::insert('quizzes', [
    'course_id' => $courseId,
    'title' => 'Sensory Calibration Final Exam',
    'time_limit_minutes' => 15,
    'passing_score' => 75,
    'max_attempts' => 2,
    'is_published' => 1,
    'created_at' => date('Y-m-d H:i:s')
]);

// Q1: Single choice (2 pts)
$q1Id = \App\Core\Database::insert('quiz_questions', [
    'quiz_id' => $quizId,
    'question_text' => 'What is the optimal water brewing temperature range according to SCA standards?',
    'question_type' => 'single_choice',
    'points' => 2,
    'explanation' => 'SCA standards recommend 90°C to 96°C.',
    'sort_order' => 1
]);
$q1Opt1 = \App\Core\Database::insert('quiz_options', ['question_id' => $q1Id, 'option_text' => '80°C - 85°C', 'is_correct' => 0, 'sort_order' => 1]);
$q1Opt2 = \App\Core\Database::insert('quiz_options', ['question_id' => $q1Id, 'option_text' => '90°C - 96°C', 'is_correct' => 1, 'sort_order' => 2]);
$q1Opt3 = \App\Core\Database::insert('quiz_options', ['question_id' => $q1Id, 'option_text' => '98°C - 100°C', 'is_correct' => 0, 'sort_order' => 3]);

// Q2: True/False (1 pt)
$q2Id = \App\Core\Database::insert('quiz_questions', [
    'quiz_id' => $quizId,
    'question_text' => 'Over-extraction generally results in excessive bitterness and astringency.',
    'question_type' => 'true_false',
    'points' => 1,
    'explanation' => 'Over-extracted coffee extracts heavier bitter tannins.',
    'sort_order' => 2
]);
$q2OptTrue = \App\Core\Database::insert('quiz_options', ['question_id' => $q2Id, 'option_text' => 'True', 'is_correct' => 1, 'sort_order' => 1]);
$q2OptFalse = \App\Core\Database::insert('quiz_options', ['question_id' => $q2Id, 'option_text' => 'False', 'is_correct' => 0, 'sort_order' => 2]);

// Q3: Single Choice (1 pt)
$q3Id = \App\Core\Database::insert('quiz_questions', [
    'quiz_id' => $quizId,
    'question_text' => 'Which Rwandan coffee variety is renowned for floral and citrus notes?',
    'question_type' => 'single_choice',
    'points' => 1,
    'explanation' => 'Red Bourbon is the dominant specialty arabica in Rwanda.',
    'sort_order' => 3
]);
$q3Opt1 = \App\Core\Database::insert('quiz_options', ['question_id' => $q3Id, 'option_text' => 'Red Bourbon', 'is_correct' => 1, 'sort_order' => 1]);
$q3Opt2 = \App\Core\Database::insert('quiz_options', ['question_id' => $q3Id, 'option_text' => 'Robusta 254', 'is_correct' => 0, 'sort_order' => 2]);

assertTest("Quiz and questions created", $quizId > 0 && $q1Id > 0 && $q2Id > 0 && $q3Id > 0);

// 2. Submit Passing Exam (100% score: 4/4 pts)
$passAnswers = [
    $q1Id => $q1Opt2,
    $q2Id => $q2OptTrue,
    $q3Id => $q3Opt1
];

$passResult = \App\Services\QuizService::submitAttempt($userId, $quizId, $passAnswers, 120);
assertTest("Passing exam auto-graded with 100% score", $passResult['success'] === true && (float)$passResult['percentage'] === 100.00 && $passResult['is_passed'] === true);
assertTest("Passing exam auto-triggered certificate generation", !empty($passResult['certificate_number']));

// 3. Verify Certificate Record in Database
$certNumber = $passResult['certificate_number'];
$cert = \App\Models\Certificate::findByNumber($certNumber);
assertTest("Certificate record exists with BBA-CERT- format", !empty($cert) && str_starts_with($cert['certificate_number'], 'BBA-CERT-'));
assertTest("Certificate has 64-char public hash", strlen($cert['public_hash']) === 64);
assertTest("Certificate status is 'valid'", $cert['status'] === 'valid');

// 4. Verify Certificate Public Lookup
$verifyRes = \App\Services\CertificateService::verifyCertificate($certNumber, '192.168.1.1', 'Mozilla/5.0');
assertTest("Public verification by serial number succeeds", $verifyRes['valid'] === true && $verifyRes['certificate']['student_name'] === $cert['student_name']);
assertTest("LinkedIn Share URL generated", !empty($verifyRes['linkedInUrl']) && str_contains($verifyRes['linkedInUrl'], 'linkedin.com'));

// 5. Verify Certificate Public Lookup by Hash
$verifyHashRes = \App\Services\CertificateService::verifyCertificate($cert['public_hash']);
assertTest("Public verification by 64-char hash succeeds", $verifyHashRes['valid'] === true);

// 6. Check Verification Audit Log
$verificationLogsCount = (int)(\App\Core\Database::fetchValue("SELECT COUNT(*) FROM certificate_verifications WHERE certificate_id = :cid", ['cid' => $cert['id']]) ?: 0);
assertTest("Verification audit entries recorded", $verificationLogsCount >= 1);

// 7. Revoke Certificate
$revokeRes = \App\Services\CertificateService::revokeCertificate((int)$cert['id'], 'Testing revocation audit trail.');
assertTest("Certificate revoked successfully", $revokeRes['success'] === true);

$verifyRevoked = \App\Services\CertificateService::verifyCertificate($certNumber);
assertTest("Revoked certificate is flagged as invalid in verification portal", $verifyRevoked['valid'] === false && $verifyRevoked['status'] === 'revoked');

// 8. Reissue / Restore Certificate
$reissueRes = \App\Services\CertificateService::reissueCertificate((int)$cert['id']);
assertTest("Certificate reissued to valid status", $reissueRes['success'] === true);

$verifyRestored = \App\Services\CertificateService::verifyCertificate($certNumber);
assertTest("Restored certificate is valid again", $verifyRestored['valid'] === true && $verifyRestored['status'] === 'valid');

// 9. Check KPI Analytics & CSV Export
$kpis = \App\Services\CertificateService::getCertificateKpis();
assertTest("Certificate KPI metrics populated", $kpis['total_issued'] >= 1 && $kpis['total_valid'] >= 1);

$csv = \App\Services\CertificateService::exportCertificatesCsv();
assertTest("Certificates CSV export formatted properly", str_contains($csv, 'Certificate #') && str_contains($csv, $certNumber));

// 10. Clean up test records
\App\Core\Database::query("DELETE FROM certificate_verifications WHERE certificate_id = :cid", ['cid' => $cert['id']]);
\App\Core\Database::query("DELETE FROM certificates WHERE id = :cid", ['cid' => $cert['id']]);
\App\Core\Database::query("DELETE FROM quiz_answers WHERE attempt_id = :aid", ['aid' => $passResult['attempt_id']]);
\App\Core\Database::query("DELETE FROM quiz_attempts WHERE id = :aid", ['aid' => $passResult['attempt_id']]);
\App\Core\Database::query("DELETE FROM quiz_options WHERE question_id IN ({$q1Id}, {$q2Id}, {$q3Id})");
\App\Core\Database::query("DELETE FROM quiz_questions WHERE id IN ({$q1Id}, {$q2Id}, {$q3Id})");
\App\Core\Database::query("DELETE FROM quizzes WHERE id = :qid", ['qid' => $quizId]);
\App\Core\Database::query("DELETE FROM enrollments WHERE id = :eid", ['eid' => $enrollmentId]);
\App\Core\Database::query("DELETE FROM courses WHERE id = :cid", ['cid' => $courseId]);

echo "\n=========================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "=========================================================\n";
