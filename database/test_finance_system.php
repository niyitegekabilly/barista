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
require BASE_PATH . '/app/Services/OrderService.php';
require BASE_PATH . '/app/Services/FinanceService.php';

echo "=========================================================\n";
echo "Beyond Barista Academy — Finance & Orders Verification\n";
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

// 0. Setup test user & course
$testUser = \App\Core\Database::fetchOne("SELECT id FROM users LIMIT 1");
$userId = $testUser ? (int)$testUser['id'] : 1;

$testCategory = \App\Core\Database::fetchOne("SELECT id FROM categories LIMIT 1");
$categoryId = $testCategory ? (int)$testCategory['id'] : 1;

$courseId = \App\Core\Database::insert('courses', [
    'title' => 'Specialty Espresso Mastery ' . time(),
    'slug' => 'specialty-espresso-' . time(),
    'category_id' => $categoryId,
    'price' => 50000.00,
    'discount_price' => 45000.00,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

// 1. Test Coupon Engine
$couponCode = 'BARISTA20_' . time();
\App\Core\Database::insert('coupons', [
    'code' => $couponCode,
    'discount_type' => 'percentage',
    'discount_value' => 20.00,
    'min_spend' => 10000.00,
    'max_uses' => 10,
    'uses_count' => 0,
    'is_active' => 1,
    'created_at' => date('Y-m-d H:i:s')
]);

$couponRes = \App\Models\Coupon::findValid($couponCode, 45000.00, $courseId, $userId);
assertTest("Coupon validation percentage calculation", $couponRes['valid'] === true && (float)$couponRes['discount_amount'] === 9000.00, "Discount: " . ($couponRes['discount_amount'] ?? 0));

// Test Min Spend Rejection
$minSpendRes = \App\Models\Coupon::findValid($couponCode, 5000.00, $courseId, $userId);
assertTest("Coupon minimum spend enforcement", $minSpendRes['valid'] === false, "Properly rejected: " . ($minSpendRes['message'] ?? ''));

// 2. Test Order Creation with Coupon
$billingData = [
    'name' => 'Jean Luc Barista',
    'email' => 'jeanluc@example.rw',
    'phone' => '0788123456',
    'address' => 'Kigali, Rwanda'
];

$orderRes = \App\Services\OrderService::createCourseOrder($userId, $courseId, $billingData, $couponCode, 'momo');
assertTest("Order creation with coupon & MoMo gateway", $orderRes['success'] === true, $orderRes['message'] ?? '');
$order = $orderRes['order'] ?? null;
$orderId = (int)$order['id'];

assertTest("Order final amount mathematically correct", (float)$order['final_amount'] === 36000.00, "Final amount: {$order['final_amount']}");

// 3. Test Atomic Order Completion & Verification
$payment = \App\Core\Database::fetchOne("SELECT * FROM payments WHERE order_id = :oid LIMIT 1", ['oid' => $orderId]);
$paymentId = (int)$payment['id'];

$completeRes = \App\Services\OrderService::completeOrder($orderId, $paymentId, 'MOMO-TX-123456');
assertTest("Atomic order completion workflow", $completeRes === true);

// Verify Order, Enrollment, Invoice, Receipt, and Ledger
$completedOrder = \App\Models\Order::findWithRelations($orderId);
assertTest("Order marked as completed and paid", $completedOrder['status'] === 'completed' && $completedOrder['payment_status'] === 'paid');

$enrollment = \App\Core\Database::fetchOne("SELECT * FROM enrollments WHERE user_id = :uid AND course_id = :cid", ['uid' => $userId, 'cid' => $courseId]);
assertTest("Active course enrollment created automatically", $enrollment && $enrollment['status'] === 'active');

$invoice = \App\Core\Database::fetchOne("SELECT * FROM invoices WHERE order_id = :oid", ['oid' => $orderId]);
assertTest("Invoice issued with unique BBA-INV number", !empty($invoice) && str_starts_with($invoice['invoice_number'], 'BBA-INV-'), "Invoice #: " . ($invoice['invoice_number'] ?? ''));

$receipt = \App\Core\Database::fetchOne("SELECT * FROM receipts WHERE payment_id = :pid", ['pid' => $paymentId]);
assertTest("Payment Receipt generated with BBA-REC number", !empty($receipt) && str_starts_with($receipt['receipt_number'], 'BBA-REC-'), "Receipt #: " . ($receipt['receipt_number'] ?? ''));

$ledgerCredit = \App\Core\Database::fetchOne("SELECT * FROM financial_transactions WHERE order_id = :oid AND direction = 'credit'", ['oid' => $orderId]);
assertTest("Double-entry financial ledger recorded credit", !empty($ledgerCredit) && (float)$ledgerCredit['amount'] === 36000.00);

// 4. Test Partial and Full Refund Workflows
$partialRefund = \App\Services\OrderService::processRefund($orderId, 10000.00, 'Partial customer compensation', $userId);
assertTest("Partial refund processing", $partialRefund['success'] === true, $partialRefund['message'] ?? '');

$orderAfterPartial = \App\Models\Order::findWithRelations($orderId);
assertTest("Remaining refundable calculation after partial refund", (float)$orderAfterPartial['remaining_refundable'] === 26000.00, "Remaining: " . $orderAfterPartial['remaining_refundable']);

// Prevent over-refund
$overRefund = \App\Services\OrderService::processRefund($orderId, 30000.00, 'Over refund attempt', $userId);
assertTest("Over-refund attempt rejected", $overRefund['success'] === false, "Rejection: " . ($overRefund['message'] ?? ''));

// Complete remaining refund with enrollment revocation
$finalRefund = \App\Services\OrderService::processRefund($orderId, 26000.00, 'Full cancellation', $userId, true);
assertTest("Final refund execution & enrollment revocation", $finalRefund['success'] === true);

$droppedEnrollment = \App\Core\Database::fetchOne("SELECT status FROM enrollments WHERE id = :id", ['id' => $enrollment['id']]);
assertTest("Enrollment revoked on full refund", $droppedEnrollment['status'] === 'dropped', "Status: " . ($droppedEnrollment['status'] ?? ''));

// 5. Test Free Course 1-Click Enrollment
$freeCourseId = \App\Core\Database::insert('courses', [
    'title' => 'Free Barista Orientation ' . time(),
    'slug' => 'free-barista-orientation-' . time(),
    'category_id' => $categoryId,
    'price' => 0.00,
    'is_free' => 1,
    'is_published' => 1,
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);

$freeOrder = \App\Services\OrderService::createCourseOrder($userId, $freeCourseId, $billingData);
assertTest("Free course enrollment requires zero payment", $freeOrder['success'] === true && (float)$freeOrder['order']['final_amount'] === 0.00);

$freeEnrollment = \App\Core\Database::fetchOne("SELECT status FROM enrollments WHERE user_id = :uid AND course_id = :cid", ['uid' => $userId, 'cid' => $freeCourseId]);
assertTest("Free course enrollment granted immediately", $freeEnrollment && $freeEnrollment['status'] === 'active');

// 6. Test Financial Dashboard KPI Calculation & CSV Export
$kpis = \App\Services\FinanceService::getDashboardKpis();
assertTest("Financial dashboard KPIs populated", isset($kpis['gross_revenue'], $kpis['net_revenue'], $kpis['total_orders']), "Gross: {$kpis['gross_revenue']}");

$csv = \App\Services\FinanceService::exportOrdersCsv();
assertTest("Orders CSV export contains records", !empty($csv) && str_contains($csv, 'Order #') && str_contains($csv, 'Final Total'));

// Clean up test records
\App\Core\Database::query("DELETE FROM enrollments WHERE course_id IN ({$courseId}, {$freeCourseId})");
\App\Core\Database::query("DELETE FROM receipts WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM refunds WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM financial_transactions WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM payments WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM invoices WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM order_items WHERE order_id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM orders WHERE id = :oid", ['oid' => $orderId]);
\App\Core\Database::query("DELETE FROM courses WHERE id IN ({$courseId}, {$freeCourseId})");
\App\Core\Database::query("DELETE FROM coupons WHERE code = :c", ['c' => $couponCode]);

echo "\n=========================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "=========================================================\n";
