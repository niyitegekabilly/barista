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
require BASE_PATH . '/app/Models/Invoice.php';
require BASE_PATH . '/app/Models/Receipt.php';
require BASE_PATH . '/app/Models/Refund.php';
require BASE_PATH . '/app/Models/FinancialTransaction.php';
require BASE_PATH . '/app/Models/AuditLog.php';
require BASE_PATH . '/app/Models/Notification.php';
require BASE_PATH . '/app/Services/VideoService.php';
require BASE_PATH . '/app/Services/ClassroomService.php';
require BASE_PATH . '/app/Services/MembershipService.php';
require BASE_PATH . '/app/Services/CouponService.php';
require BASE_PATH . '/app/Services/OrderService.php';
require BASE_PATH . '/app/Services/FinanceService.php';

echo "=========================================================\n";
echo "Beyond Barista Academy — Classroom & Learning Test Suite\n";
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

// 0. Setup test user & category
$testUser = \App\Core\Database::fetchOne("SELECT id FROM users LIMIT 1");
$userId = $testUser ? (int)$testUser['id'] : 1;

$testCat = \App\Core\Database::fetchOne("SELECT id FROM categories LIMIT 1");
$categoryId = $testCat ? (int)$testCat['id'] : 1;

// Create Course with 2 Modules and 4 Lessons
$courseSlug = 'cupping-mastery-' . time();
$courseId = \App\Core\Database::insert('courses', [
    'title' => 'Cupping & Sensory Mastery ' . time(),
    'slug' => $courseSlug,
    'category_id' => $categoryId,
    'price' => 75000.00,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

$module1Id = \App\Core\Database::insert('modules', [
    'course_id' => $courseId,
    'title' => 'Module 1: Water Chemistry & Grind Calibration',
    'sort_order' => 1
]);

$lesson1Id = \App\Core\Database::insert('lessons', [
    'module_id' => $module1Id,
    'course_id' => $courseId,
    'title' => 'TDS and Extraction Yield Science',
    'slug' => 'tds-extraction-science-' . time(),
    'lesson_type' => 'video',
    'video_provider' => 'youtube',
    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'duration_minutes' => 15,
    'is_published' => 1,
    'sort_order' => 1
]);

$lesson2Id = \App\Core\Database::insert('lessons', [
    'module_id' => $module1Id,
    'course_id' => $courseId,
    'title' => 'SCA Cupping Protocol Form Breakdown',
    'slug' => 'sca-cupping-protocol-' . time(),
    'lesson_type' => 'pdf',
    'pdf_path' => 'sca_protocol.pdf',
    'duration_minutes' => 20,
    'is_published' => 1,
    'sort_order' => 2
]);

// 1. Test Access Resolution - Non-Enrolled User Denied
$deniedRes = \App\Services\ClassroomService::getClassroomData(999999, $courseSlug);
assertTest("Non-enrolled guest denied access to classroom", $deniedRes['success'] === false && $deniedRes['code'] === 403);

// 2. Direct Enrollment Access Granted
$enrollmentId = \App\Core\Database::insert('enrollments', [
    'user_id' => $userId,
    'course_id' => $courseId,
    'status' => 'active',
    'progress_percent' => 0,
    'enrolled_at' => date('Y-m-d H:i:s')
]);

$allowedRes = \App\Services\ClassroomService::getClassroomData($userId, $courseSlug);
assertTest("Enrolled student granted full classroom access", $allowedRes['success'] === true && !empty($allowedRes['current_lesson']));
assertTest("Curriculum modules and lessons loaded", count($allowedRes['modules']) >= 1 && count($allowedRes['all_lessons']) === 2);

// 3. Real-time Video Playback Progress & Heartbeat
$progressRes = \App\Services\ClassroomService::saveProgress($userId, $enrollmentId, $lesson1Id, 185, 45, false, 1.25);
assertTest("Video progress heartbeat recorded playback position & speed", $progressRes['success'] === true);

$savedProgress = \App\Core\Database::fetchOne("SELECT * FROM lesson_progress WHERE enrollment_id = :eid AND lesson_id = :lid", ['eid' => $enrollmentId, 'lid' => $lesson1Id]);
assertTest("Lesson progress stored in DB accurately", (int)$savedProgress['last_position_seconds'] === 185 && (int)$savedProgress['time_spent_seconds'] === 45);

// 4. Mark Lesson 1 Complete & Check Recalculated Course Progress (50%)
$comp1Res = \App\Services\ClassroomService::saveProgress($userId, $enrollmentId, $lesson1Id, 0, 0, true);
assertTest("Lesson 1 completed updates course progress to 50%", $comp1Res['success'] === true && $comp1Res['progress_percent'] === 50);

// 5. Complete Lesson 2 -> Triggers 100% Course Completion
$comp2Res = \App\Services\ClassroomService::saveProgress($userId, $enrollmentId, $lesson2Id, 0, 0, true);
assertTest("Lesson 2 completed updates course progress to 100%", $comp2Res['success'] === true && $comp2Res['progress_percent'] === 100);

$enrollmentAfter100 = \App\Core\Database::fetchOne("SELECT * FROM enrollments WHERE id = :id", ['id' => $enrollmentId]);
assertTest("Course marked completed with completed_at timestamp", !empty($enrollmentAfter100['completed_at']) && $enrollmentAfter100['status'] === 'completed');

// 6. Test Timestamped Private Notes
$noteRes = \App\Services\ClassroomService::saveNote($userId, $courseId, $lesson1Id, "Grind size 24 clicks for washed Bourbon.", 185);
assertTest("Private note saved with video timestamp", $noteRes['success'] === true && $noteRes['timestamp_formatted'] === '03:05');

$noteId = (int)$noteRes['note_id'];
$delNote = \App\Services\ClassroomService::deleteNote($userId, $noteId);
assertTest("Student note deletion", $delNote === true);

// 7. Test Community Q&A Discussions
$discRes = \App\Services\ClassroomService::postDiscussion($userId, $courseId, $lesson1Id, "What water temperature do you recommend for natural processed Rwandan coffee?");
assertTest("Community discussion question posted", $discRes['success'] === true && $discRes['discussion_id'] > 0);

// 8. Test Bookmarks
$bmRes = \App\Services\ClassroomService::toggleBookmark($userId, $lesson1Id, "Dial-in step", 120);
assertTest("Lesson moment bookmarked", $bmRes['success'] === true && $bmRes['action'] === 'added');

$bmRemoveRes = \App\Services\ClassroomService::toggleBookmark($userId, $lesson1Id, "Dial-in step", 120);
assertTest("Lesson moment bookmark toggled off", $bmRemoveRes['success'] === true && $bmRemoveRes['action'] === 'removed');

// 9. Clean up test records
\App\Core\Database::query("DELETE FROM lesson_bookmarks WHERE lesson_id IN ({$lesson1Id}, {$lesson2Id})");
\App\Core\Database::query("DELETE FROM lesson_discussions WHERE lesson_id IN ({$lesson1Id}, {$lesson2Id})");
\App\Core\Database::query("DELETE FROM lesson_notes WHERE lesson_id IN ({$lesson1Id}, {$lesson2Id})");
\App\Core\Database::query("DELETE FROM lesson_progress WHERE enrollment_id = :eid", ['eid' => $enrollmentId]);
\App\Core\Database::query("DELETE FROM enrollments WHERE id = :eid", ['eid' => $enrollmentId]);
\App\Core\Database::query("DELETE FROM lessons WHERE id IN ({$lesson1Id}, {$lesson2Id})");
\App\Core\Database::query("DELETE FROM modules WHERE id = :mid", ['mid' => $module1Id]);
\App\Core\Database::query("DELETE FROM courses WHERE id = :cid", ['cid' => $courseId]);

echo "\n=========================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "=========================================================\n";
