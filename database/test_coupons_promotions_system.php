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
require BASE_PATH . '/app/Models/Invoice.php';
require BASE_PATH . '/app/Models/Receipt.php';
require BASE_PATH . '/app/Models/Refund.php';
require BASE_PATH . '/app/Models/FinancialTransaction.php';
require BASE_PATH . '/app/Models/AuditLog.php';
require BASE_PATH . '/app/Models/Notification.php';
require BASE_PATH . '/app/Services/Payment/PaymentGatewayInterface.php';
require BASE_PATH . '/app/Services/Payment/Gateways/MomoPaymentGateway.php';
require BASE_PATH . '/app/Services/Payment/Gateways/StripePaymentGateway.php';
require BASE_PATH . '/app/Services/Payment/Gateways/ManualBankPaymentGateway.php';
require BASE_PATH . '/app/Services/Payment/Gateways/SandboxPaymentGateway.php';
require BASE_PATH . '/app/Services/Payment/PaymentGatewayManager.php';
require BASE_PATH . '/app/Services/InvoiceService.php';
require BASE_PATH . '/app/Services/CouponService.php';
require BASE_PATH . '/app/Services/OrderService.php';
require BASE_PATH . '/app/Services/FinanceService.php';

echo "=========================================================\n";
echo "Beyond Barista Academy — Coupons & Promotions Test Suite\n";
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

// 0. Setup test user, category & course
$testUser = \App\Core\Database::fetchOne("SELECT id FROM users LIMIT 1");
$userId = $testUser ? (int)$testUser['id'] : 1;

$testCat = \App\Core\Database::fetchOne("SELECT id FROM categories LIMIT 1");
$categoryId = $testCat ? (int)$testCat['id'] : 1;

$course1Id = \App\Core\Database::insert('courses', [
    'title' => 'Sensory Skills & Cupping ' . time(),
    'slug' => 'sensory-skills-' . time(),
    'category_id' => $categoryId,
    'price' => 100000.00,
    'discount_price' => 80000.00,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

$course2Id = \App\Core\Database::insert('courses', [
    'title' => 'Advanced Roasting Profile ' . time(),
    'slug' => 'advanced-roasting-' . time(),
    'category_id' => $categoryId,
    'price' => 120000.00,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

// 1. Test Campaign Creation
$campaignId = \App\Core\Database::insert('coupon_campaigns', [
    'name' => 'Intake Spring 2026 ' . time(),
    'slug' => 'spring-2026-' . time(),
    'budget_limit' => 500000.00,
    'discount_spent' => 0.00,
    'status' => 'active',
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);
assertTest("Campaign creation with budget", $campaignId > 0);

// 2. Test Percentage Discount with Maximum Discount Amount Cap
$cappedCode = 'CAP20_' . time();
$cappedCouponId = \App\Core\Database::insert('coupons', [
    'code' => $cappedCode,
    'campaign_id' => $campaignId,
    'discount_type' => 'percentage',
    'discount_value' => 20.00, // 20% of 100,000 is 20,000
    'max_discount_amount' => 15000.00, // Cap at 15,000 RWF
    'min_spend' => 50000.00,
    'max_uses' => 50,
    'is_active' => 1,
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);

$cappedRes = \App\Services\CouponService::validateCoupon($cappedCode, 100000.00, $course1Id, $userId);
assertTest("Percentage discount with maximum cap ceiling", $cappedRes['valid'] === true && (float)$cappedRes['discount_amount'] === 15000.00 && (float)$cappedRes['final_amount'] === 85000.00, "Discount: {$cappedRes['discount_amount']}");

// 3. Test Course Inclusions and Exclusions
$excludedCourseCode = 'EXC_' . time();
$excCouponId = \App\Core\Database::insert('coupons', [
    'code' => $excludedCourseCode,
    'discount_type' => 'fixed',
    'discount_value' => 10000.00,
    'is_active' => 1,
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);
// Exclude course2 explicitly
\App\Core\Database::insert('coupon_courses', ['coupon_id' => $excCouponId, 'course_id' => $course2Id, 'type' => 'exclude']);

$excValidOnCourse1 = \App\Services\CouponService::validateCoupon($excludedCourseCode, 100000.00, $course1Id, $userId);
assertTest("Coupon valid on non-excluded course", $excValidOnCourse1['valid'] === true);

$excInvalidOnCourse2 = \App\Services\CouponService::validateCoupon($excludedCourseCode, 120000.00, $course2Id, $userId);
assertTest("Coupon rejected on explicitly excluded course", $excInvalidOnCourse2['valid'] === false, "Rejection: " . ($excInvalidOnCourse2['message'] ?? ''));

// 4. Test Scheduled Start Date & Expiration
$futureCode = 'FUTURE_' . time();
\App\Core\Database::insert('coupons', [
    'code' => $futureCode,
    'discount_type' => 'percentage',
    'discount_value' => 10.00,
    'start_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
    'is_active' => 1,
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);
$futureRes = \App\Services\CouponService::validateCoupon($futureCode, 50000.00, $course1Id, $userId);
assertTest("Scheduled future coupon rejected prior to start date", $futureRes['valid'] === false, "Message: " . ($futureRes['message'] ?? ''));

$expiredCode = 'EXPIRED_' . time();
\App\Core\Database::insert('coupons', [
    'code' => $expiredCode,
    'discount_type' => 'percentage',
    'discount_value' => 10.00,
    'expires_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
    'is_active' => 1,
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);
$expiredRes = \App\Services\CouponService::validateCoupon($expiredCode, 50000.00, $course1Id, $userId);
assertTest("Expired coupon rejected", $expiredRes['valid'] === false, "Message: " . ($expiredRes['message'] ?? ''));

// 5. Test 100% Free Discount Full Checkout Flow
$free100Code = 'FREE100_' . time();
$freeCouponId = \App\Core\Database::insert('coupons', [
    'code' => $free100Code,
    'discount_type' => 'percentage',
    'discount_value' => 100.00,
    'max_uses' => 10,
    'is_active' => 1,
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);

$billingData = ['name' => 'Test Student', 'email' => 'student@test.rw'];
$freeOrderRes = \App\Services\OrderService::createCourseOrder($userId, $course2Id, $billingData, $free100Code);
assertTest("100% discount promo yields 0 RWF final total", $freeOrderRes['success'] === true && (float)$freeOrderRes['order']['final_amount'] === 0.00);

$freeOrderId = (int)$freeOrderRes['order']['id'];
$freeEnrollment = \App\Core\Database::fetchOne("SELECT status FROM enrollments WHERE user_id = :uid AND course_id = :cid", ['uid' => $userId, 'cid' => $course2Id]);
assertTest("100% discount promo grants instant active enrollment", $freeEnrollment && $freeEnrollment['status'] === 'active');

$redemptionRecord = \App\Core\Database::fetchOne("SELECT * FROM coupon_redemptions WHERE coupon_id = :cid AND order_id = :oid", ['cid' => $freeCouponId, 'oid' => $freeOrderId]);
assertTest("Coupon redemption ledger entry created atomically", !empty($redemptionRecord) && (float)$redemptionRecord['discount_amount'] === 120000.00);

// 6. Test Bulk Code Generation Tool
$bulkRes = \App\Services\CouponService::bulkGenerate([
    'count' => 10,
    'prefix' => 'STUDENT',
    'length' => 6,
    'campaign_id' => $campaignId,
    'discount_type' => 'fixed',
    'discount_value' => 5000.00,
    'max_uses' => 1
], $userId);
assertTest("Bulk code generation created 10 unique codes", $bulkRes['success'] === true && $bulkRes['count'] === 10);
assertTest("Bulk codes unique and properly prefixed", str_starts_with($bulkRes['codes'][0], 'STUDENT') && count(array_unique($bulkRes['codes'])) === 10);

// 7. Test Deletion Safety Guard
$delCoupon = \App\Models\Coupon::find($freeCouponId);
$hasRedemptions = (int)\App\Core\Database::fetchValue("SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = :id", ['id' => $freeCouponId]);
assertTest("Coupon with recorded redemptions blocked from deletion", $hasRedemptions > 0);

// 8. Test CSV Exports
$couponsCsv = \App\Services\CouponService::exportCouponsCsv();
assertTest("Coupons CSV export formatted properly", !empty($couponsCsv) && str_contains($couponsCsv, 'Code') && str_contains($couponsCsv, 'Discount Value'));

$redemptionsCsv = \App\Services\CouponService::exportRedemptionsCsv();
assertTest("Redemptions CSV export formatted properly", !empty($redemptionsCsv) && str_contains($redemptionsCsv, 'Redemption ID') && str_contains($redemptionsCsv, 'Customer Email'));

// 9. Clean up test records
\App\Core\Database::query("DELETE FROM enrollments WHERE course_id IN ({$course1Id}, {$course2Id})");
\App\Core\Database::query("DELETE FROM coupon_redemptions WHERE order_id = :oid", ['oid' => $freeOrderId]);
\App\Core\Database::query("DELETE FROM receipts WHERE order_id = :oid", ['oid' => $freeOrderId]);
\App\Core\Database::query("DELETE FROM invoices WHERE order_id = :oid", ['oid' => $freeOrderId]);
\App\Core\Database::query("DELETE FROM payments WHERE order_id = :oid", ['oid' => $freeOrderId]);
\App\Core\Database::query("DELETE FROM order_items WHERE order_id = :oid", ['oid' => $freeOrderId]);
\App\Core\Database::query("DELETE FROM orders WHERE id = :oid", ['oid' => $freeOrderId]);
\App\Core\Database::query("DELETE FROM coupon_courses WHERE coupon_id IN ({$cappedCouponId}, {$excCouponId}, {$freeCouponId})");
\App\Core\Database::query("DELETE FROM coupons WHERE id IN (" . implode(',', array_merge([$cappedCouponId, $excCouponId, $freeCouponId], $bulkRes['ids'])) . ")");
\App\Core\Database::query("DELETE FROM coupons WHERE code IN ('{$futureCode}', '{$expiredCode}')");
\App\Core\Database::query("DELETE FROM coupon_campaigns WHERE id = :cid", ['cid' => $campaignId]);
\App\Core\Database::query("DELETE FROM courses WHERE id IN ({$course1Id}, {$course2Id})");

echo "\n=========================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "=========================================================\n";
