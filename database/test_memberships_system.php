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
require BASE_PATH . '/app/Services/MembershipService.php';
require BASE_PATH . '/app/Services/OrderService.php';
require BASE_PATH . '/app/Services/FinanceService.php';

echo "=========================================================\n";
echo "Beyond Barista Academy — Memberships & Subscriptions Test Suite\n";
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

// 0. Setup test user & courses
$testUser = \App\Core\Database::fetchOne("SELECT id FROM users LIMIT 1");
$userId = $testUser ? (int)$testUser['id'] : 1;

$testCat = \App\Core\Database::fetchOne("SELECT id FROM categories LIMIT 1");
$categoryId = $testCat ? (int)$testCat['id'] : 1;

$courseAId = \App\Core\Database::insert('courses', [
    'title' => 'Espresso Extraction Science ' . time(),
    'slug' => 'espresso-science-' . time(),
    'category_id' => $categoryId,
    'price' => 85000.00,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

$courseBId = \App\Core\Database::insert('courses', [
    'title' => 'Green Coffee Sourcing ' . time(),
    'slug' => 'green-coffee-' . time(),
    'category_id' => $categoryId,
    'price' => 95000.00,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

// 1. Create Membership Plan with Specific Course Access
$planSlug = 'pro-test-' . time();
$planId = \App\Core\Database::insert('membership_plans', [
    'name' => 'Pro Barista Monthly ' . time(),
    'slug' => $planSlug,
    'description' => 'Test monthly membership tier',
    'price' => 30000.00,
    'currency' => 'RWF',
    'billing_interval' => 'month',
    'tier_level' => 2,
    'trial_period_days' => 0,
    'grace_period_days' => 3,
    'course_access_type' => 'specific_courses',
    'has_certificate_access' => 1,
    'has_live_workshops' => 1,
    'is_active' => 1,
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);
assertTest("Membership Plan creation", $planId > 0);

// Map Course A into Plan
\App\Core\Database::insert('membership_plan_courses', ['plan_id' => $planId, 'course_id' => $courseAId]);

// 2. Test Membership Order Checkout Initiation
$billingData = ['name' => 'Jean Pierre', 'email' => 'jean@test.rw', 'phone' => '0788111222'];
$orderRes = \App\Services\OrderService::createMembershipOrder($userId, $planId, $billingData, null, 'sandbox');
assertTest("Membership order initiated with sandbox gateway", $orderRes['success'] === true && !empty($orderRes['order']));

$orderId = (int)$orderRes['order']['id'];
$order = \App\Models\Order::findWithRelations($orderId);
assertTest("Order item type is 'membership'", !empty($order['items'][0]) && $order['items'][0]['item_type'] === 'membership');

// 3. Verify Automatic Subscription Activation upon Order Completion
$subRecord = \App\Core\Database::fetchOne("SELECT * FROM memberships WHERE order_id = :oid", ['oid' => $orderId]);
assertTest("Active subscription created with BBA-SUB number", !empty($subRecord) && str_starts_with($subRecord['subscription_number'], 'BBA-SUB-') && $subRecord['status'] === 'active');

$subId = (int)$subRecord['id'];

// Verify Renewal Log Entry
$renewalRecord = \App\Core\Database::fetchOne("SELECT * FROM membership_renewals WHERE membership_id = :sid", ['sid' => $subId]);
assertTest("Membership renewal ledger entry logged", !empty($renewalRecord) && (float)$renewalRecord['amount'] === 30000.00);

// 4. Test Dynamic Access Control & Content Gating Engine
$canAccessCourseA = \App\Services\MembershipService::canUserAccessCourse($userId, $courseAId);
assertTest("Access Control: Member granted access to mapped Course A", $canAccessCourseA === true);

$canAccessCourseB = \App\Services\MembershipService::canUserAccessCourse($userId, $courseBId);
assertTest("Access Control: Member denied access to unmapped Course B", $canAccessCourseB === false);

// 5. Test Subscription Manual Date Extension
$extRes = \App\Services\MembershipService::extendSubscription($subId, 15, 'Complimentary test extension');
assertTest("Admin manual extension added 15 days", $extRes['success'] === true);

$subAfterExt = \App\Models\Membership::find($subId);
assertTest("Subscription end date updated in database", $subAfterExt['end_date'] === $extRes['new_end_date']);

// 6. Test Subscription Cancellation
$cancelRes = \App\Services\MembershipService::cancelSubscription($subId, 'Student requested cancel', false, $userId);
assertTest("Subscription auto-renew cancellation processed", $cancelRes['success'] === true);

$subAfterCancel = \App\Models\Membership::find($subId);
assertTest("Auto-renew toggled off with access preserved until period end", (int)$subAfterCancel['auto_renew'] === 0);

// 7. Test Executive MRR Dashboard & KPIs
$kpis = \App\Services\MembershipService::getDashboardKpis();
assertTest("MRR calculated from active subscribers", $kpis['mrr'] >= 30000.00 && $kpis['active_subscribers'] >= 1);

// 8. Test CSV Export
$csv = \App\Services\MembershipService::exportSubscriptionsCsv();
assertTest("Subscriptions CSV export formatted properly", !empty($csv) && str_contains($csv, 'Subscription #') && str_contains($csv, 'BBA-SUB-'));

// 9. Clean up test records
\App\Core\Database::query("DELETE FROM enrollments WHERE course_id IN ({$courseAId}, {$courseBId})");
\App\Core\Database::query("DELETE FROM membership_activity_logs WHERE membership_id = :sid", ['sid' => $subId]);
\App\Core\Database::query("DELETE FROM membership_renewals WHERE membership_id = :sid", ['sid' => $subId]);
\App\Core\Database::query("DELETE FROM memberships WHERE id = :sid", ['sid' => $subId]);
\App\Core\Database::query("DELETE FROM receipts WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM invoices WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM financial_transactions WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM payments WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM order_items WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM orders WHERE id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM membership_plan_courses WHERE plan_id = :pid", ['pid' => $planId]);
\App\Core\Database::query("DELETE FROM membership_plans WHERE id = :pid", ['pid' => $planId]);
\App\Core\Database::query("DELETE FROM courses WHERE id IN ({$courseAId}, {$courseBId})");

echo "\n=========================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "=========================================================\n";
