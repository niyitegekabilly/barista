<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Course;
use App\Models\Coupon;
use App\Models\Refund;
use App\Models\FinancialTransaction;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Services\Payment\PaymentGatewayManager;

class OrderService {

    /**
     * Create an order for a course and initiate payment.
     */
    public static function createCourseOrder(
        int $userId,
        int $courseId,
        array $billingData,
        ?string $couponCode = null,
        string $paymentMethod = 'momo'
    ): array {
        $course = Course::find($courseId);
        if (!$course || empty($course['is_published'])) {
            return ['success' => false, 'message' => 'Course is not available for purchase.'];
        }

        // Check if user is already actively enrolled
        $existingEnrollment = Database::fetchOne(
            "SELECT id, status FROM enrollments WHERE user_id = :uid AND course_id = :cid AND status IN ('active', 'completed')",
            ['uid' => $userId, 'cid' => $courseId]
        );
        if ($existingEnrollment) {
            return ['success' => false, 'message' => 'You are already enrolled in this course.'];
        }

        $coursePrice = (float)($course['discount_price'] ?: $course['price']);
        $couponId = null;
        $discountAmount = 0.00;

        // Server-side coupon evaluation
        if (!empty($couponCode) && $coursePrice > 0) {
            $couponCheck = CouponService::validateCoupon($couponCode, $coursePrice, $courseId, $userId);
            if ($couponCheck['valid']) {
                $couponId = (int)$couponCheck['coupon']['id'];
                $discountAmount = (float)$couponCheck['discount_amount'];
            }
        }

        $subtotal = $coursePrice;
        $finalTotal = max(0.00, round($subtotal - $discountAmount, 2));
        $currency = 'RWF';

        // 1. Create Order
        $orderNumber = Order::generateOrderNumber();
        $isFree = ($finalTotal <= 0);

        $orderId = Database::insert('orders', [
            'order_number'    => $orderNumber,
            'user_id'         => $userId,
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount'      => 0.00,
            'fee_amount'      => 0.00,
            'total_amount'    => $subtotal,
            'final_amount'    => $finalTotal,
            'currency'        => $currency,
            'status'          => $isFree ? 'completed' : 'pending',
            'payment_status'  => $isFree ? 'paid' : 'unpaid',
            'coupon_id'       => $couponId,
            'billing_name'    => trim($billingData['name'] ?? ''),
            'billing_email'   => trim($billingData['email'] ?? ''),
            'billing_phone'   => trim($billingData['phone'] ?? ''),
            'billing_address' => trim($billingData['address'] ?? ''),
            'payment_method'  => $paymentMethod,
            'customer_notes'  => $billingData['customer_notes'] ?? null,
            'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        // 2. Insert Order Item
        Database::insert('order_items', [
            'order_id'        => $orderId,
            'item_type'       => 'course',
            'item_id'         => $courseId,
            'item_title'      => $course['title'],
            'unit_price'      => $coursePrice,
            'price'           => $coursePrice,
            'discount_amount' => $discountAmount,
            'tax_amount'      => 0.00,
            'total_amount'    => $finalTotal,
            'created_at'      => date('Y-m-d H:i:s')
        ]);

        $order = Order::findWithRelations($orderId);

        // 3. If Free Course ($0 total), complete immediately
        if ($isFree) {
            $paymentId = Database::insert('payments', [
                'order_id'              => $orderId,
                'user_id'               => $userId,
                'payment_method'        => 'free',
                'gateway'               => 'free',
                'transaction_reference' => 'FREE-ENROLL-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2))),
                'amount'                => 0.00,
                'currency'              => $currency,
                'status'                => 'successful',
                'verified_at'           => date('Y-m-d H:i:s'),
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s')
            ]);

            static::completeOrder($orderId, $paymentId, 'FREE-PROMO');

            return [
                'success' => true,
                'order' => $order,
                'redirect_url' => url('checkout/success/' . $orderNumber),
                'message' => 'Enrollment completed successfully!'
            ];
        }

        // 4. Initiate with Gateway
        $gateway = PaymentGatewayManager::get($paymentMethod) ?: PaymentGatewayManager::get('sandbox');
        $initiation = $gateway->initiatePayment($order, $billingData);

        // Insert Payment Record
        $paymentId = Database::insert('payments', [
            'order_id'              => $orderId,
            'user_id'               => $userId,
            'payment_method'        => $paymentMethod,
            'gateway'               => $gateway->getIdentifier(),
            'transaction_reference' => $initiation['transaction_reference'],
            'amount'                => $finalTotal,
            'currency'              => $currency,
            'status'                => 'pending',
            'provider_response'     => json_encode($initiation['provider_data'] ?? []),
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s')
        ]);

        AuditLog::log('order_created', 'order', $orderId, [
            'order_number' => $orderNumber,
            'final_amount' => $finalTotal,
            'payment_method' => $paymentMethod
        ]);

        // If sandbox gateway, automatically verify & complete
        if ($gateway->getIdentifier() === 'sandbox') {
            static::completeOrder($orderId, $paymentId, 'SANDBOX-AUTO-' . date('YmdHis'));
        }

        return [
            'success' => true,
            'order' => $order,
            'payment_id' => $paymentId,
            'transaction_reference' => $initiation['transaction_reference'],
            'redirect_url' => $initiation['redirect_url'] ?? url('checkout/success/' . $orderNumber),
            'message' => $initiation['message'] ?? 'Payment initiated.'
        ];
    }

    /**
     * Create a recurring membership plan purchase order.
     */
    public static function createMembershipOrder(
        int $userId,
        int $planId,
        array $billingData,
        ?string $couponCode = null,
        string $paymentMethod = 'momo'
    ): array {
        $plan = \App\Models\MembershipPlan::findWithRelations($planId);
        if (!$plan || (int)$plan['is_active'] === 0) {
            return ['success' => false, 'message' => 'Selected membership plan is not available.'];
        }

        $planPrice = (float)$plan['price'];
        $currency = $plan['currency'] ?? 'RWF';
        $discountAmount = 0.00;
        $couponId = null;

        // Apply Coupon if provided
        if (!empty($couponCode)) {
            $couponResult = CouponService::validateCoupon($couponCode, $planPrice, null, $userId);
            if ($couponResult['valid']) {
                $discountAmount = (float)$couponResult['discount_amount'];
                $couponId = (int)$couponResult['coupon']['id'];
            }
        }

        $finalTotal = max(0.00, $planPrice - $discountAmount);
        $isFree = ($finalTotal === 0.00);

        // 1. Insert Order
        $orderNumber = Order::generateOrderNumber();
        $orderId = Database::insert('orders', [
            'order_number'    => $orderNumber,
            'user_id'         => $userId,
            'subtotal_amount' => $planPrice,
            'discount_amount' => $discountAmount,
            'tax_amount'      => 0.00,
            'fee_amount'      => 0.00,
            'total_amount'    => $planPrice,
            'final_amount'    => $finalTotal,
            'currency'        => $currency,
            'status'          => $isFree ? 'completed' : 'pending',
            'payment_status'  => $isFree ? 'paid' : 'unpaid',
            'coupon_id'       => $couponId,
            'billing_name'    => trim($billingData['name'] ?? ''),
            'billing_email'   => trim($billingData['email'] ?? ''),
            'billing_phone'   => trim($billingData['phone'] ?? ''),
            'billing_address' => trim($billingData['address'] ?? ''),
            'payment_method'  => $paymentMethod,
            'customer_notes'  => $billingData['customer_notes'] ?? null,
            'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        // 2. Insert Order Item
        Database::insert('order_items', [
            'order_id'        => $orderId,
            'item_type'       => 'membership',
            'item_id'         => $planId,
            'item_title'      => $plan['name'] . ' (' . ucfirst($plan['billing_interval']) . ')',
            'unit_price'      => $planPrice,
            'price'           => $planPrice,
            'discount_amount' => $discountAmount,
            'tax_amount'      => 0.00,
            'total_amount'    => $finalTotal,
            'created_at'      => date('Y-m-d H:i:s')
        ]);

        $order = Order::findWithRelations($orderId);

        // 3. If Free Membership, complete immediately
        if ($isFree) {
            $paymentId = Database::insert('payments', [
                'order_id'              => $orderId,
                'user_id'               => $userId,
                'payment_method'        => 'free',
                'gateway'               => 'free',
                'transaction_reference' => 'FREE-SUB-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2))),
                'amount'                => 0.00,
                'currency'              => $currency,
                'status'                => 'successful',
                'verified_at'           => date('Y-m-d H:i:s'),
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s')
            ]);

            static::completeOrder($orderId, $paymentId, 'FREE-MEMBERSHIP');

            return [
                'success'      => true,
                'order'        => $order,
                'redirect_url' => url('checkout/success/' . $orderNumber),
                'message'      => 'Membership activated successfully!'
            ];
        }

        // 4. Initiate with Gateway
        $gateway = PaymentGatewayManager::get($paymentMethod) ?: PaymentGatewayManager::get('sandbox');
        $initiation = $gateway->initiatePayment($order, $billingData);

        $paymentId = Database::insert('payments', [
            'order_id'              => $orderId,
            'user_id'               => $userId,
            'payment_method'        => $paymentMethod,
            'gateway'               => $gateway->getIdentifier(),
            'transaction_reference' => $initiation['transaction_reference'],
            'amount'                => $finalTotal,
            'currency'              => $currency,
            'status'                => 'pending',
            'provider_response'     => json_encode($initiation['provider_data'] ?? []),
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s')
        ]);

        if ($gateway->getIdentifier() === 'sandbox') {
            static::completeOrder($orderId, $paymentId, 'SANDBOX-AUTO-' . date('YmdHis'));
        }

        return [
            'success'               => true,
            'order'                 => $order,
            'payment_id'            => $paymentId,
            'transaction_reference' => $initiation['transaction_reference'],
            'redirect_url'          => $initiation['redirect_url'] ?? url('checkout/success/' . $orderNumber),
            'message'               => $initiation['message'] ?? 'Payment initiated.'
        ];
    }

    /**
     * Complete an order atomically, verify payment, grant enrollment, and emit invoices/receipts.
     */
    public static function completeOrder(int $orderId, int $paymentId, ?string $gatewayTxId = null, array $rawResponse = []): bool {
        $order = Order::findWithRelations($orderId);
        $payment = Database::fetchOne("SELECT * FROM payments WHERE id = :id", ['id' => $paymentId]);

        if (!$order || !$payment) {
            return false;
        }

        // 1. Update Payment Status
        Database::update('payments', [
            'status'                 => 'successful',
            'gateway_transaction_id' => $gatewayTxId ?: $payment['transaction_reference'],
            'verified_at'            => date('Y-m-d H:i:s'),
            'provider_response'      => !empty($rawResponse) ? json_encode($rawResponse) : $payment['provider_response'],
            'updated_at'             => date('Y-m-d H:i:s')
        ], ['id' => $paymentId]);

        // 2. Update Order Status
        Database::update('orders', [
            'status'         => 'completed',
            'payment_status' => 'paid',
            'updated_at'     => date('Y-m-d H:i:s')
        ], ['id' => $orderId]);

        // 3. Record Coupon Redemption if coupon was applied
        if (!empty($order['coupon_id'])) {
            CouponService::recordRedemption(
                (int)$order['coupon_id'],
                $orderId,
                (int)$order['user_id'],
                (float)($order['subtotal_amount'] ?: $order['total_amount']),
                (float)($order['discount_amount'] ?? 0),
                (float)$payment['amount'],
                !empty($order['items'][0]['item_id']) ? (int)$order['items'][0]['item_id'] : null
            );
        }

        // 4. Create / Activate Course Enrollment(s)
        foreach ($order['items'] as $item) {
            if ($item['item_type'] === 'course') {
                $courseId = (int)$item['item_id'];
                $existing = Database::fetchOne(
                    "SELECT id FROM enrollments WHERE user_id = :uid AND course_id = :cid",
                    ['uid' => $order['user_id'], 'cid' => $courseId]
                );

                if ($existing) {
                    Database::update('enrollments', [
                        'status'      => 'active',
                        'enrolled_at' => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s')
                    ], ['id' => $existing['id']]);
                } else {
                    Database::insert('enrollments', [
                        'user_id'          => $order['user_id'],
                        'course_id'        => $courseId,
                        'status'           => 'active',
                        'progress_percent' => 0,
                        'enrolled_at'      => date('Y-m-d H:i:s'),
                        'created_at'       => date('Y-m-d H:i:s'),
                        'updated_at'       => date('Y-m-d H:i:s')
                    ]);
                }
            } elseif ($item['item_type'] === 'membership') {
                $planId = (int)$item['item_id'];
                MembershipService::createSubscriptionFromOrder(
                    $orderId,
                    (int)$order['user_id'],
                    $planId,
                    $payment['payment_method'] ?? 'momo'
                );
            }
        }

        // 5. Issue Invoice & Payment Receipt
        InvoiceService::createOrGetInvoice($orderId);
        InvoiceService::createOrGetReceipt($paymentId);

        // 6. Record Double-Entry Financial Ledger Credit
        if ((float)$payment['amount'] > 0) {
            FinancialTransaction::record([
                'order_id'   => $orderId,
                'payment_id' => $paymentId,
                'user_id'    => $order['user_id'],
                'type'       => 'charge',
                'amount'     => (float)$payment['amount'],
                'currency'   => $payment['currency'] ?? 'RWF',
                'direction'  => 'credit',
                'status'     => 'completed',
                'reference'  => $payment['transaction_reference'],
                'notes'      => 'Payment completed for order ' . $order['order_number']
            ]);
        }

        // 7. Send Notifications & Audit Log
        Notification::send(
            (int)$order['user_id'],
            'Payment Confirmed & Course Access Granted',
            'Your payment for order ' . $order['order_number'] . ' was successful. You now have full access to your curriculum.',
            url('student/classroom/' . ($order['items'][0]['course_slug'] ?? ''))
        );

        AuditLog::log('order_completed', 'order', $orderId, [
            'order_number' => $order['order_number'],
            'amount' => $payment['amount'],
            'gateway' => $payment['gateway']
        ]);

        return true;
    }

    /**
     * Process a full or partial refund with ledger debit and optional enrollment revocation.
     */
    public static function processRefund(
        int $orderId,
        float $refundAmount,
        string $reason,
        ?int $adminUserId = null,
        bool $cancelEnrollment = false
    ): array {
        $order = Order::findWithRelations($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $refundableMax = Order::getRemainingRefundableAmount($orderId);
        if ($refundAmount <= 0 || $refundAmount > $refundableMax) {
            return [
                'success' => false,
                'message' => "Refund amount must be between 1 RWF and maximum refundable: " . format_money($refundableMax, $order['currency'])
            ];
        }

        $payment = $order['latest_payment'];
        $paymentId = $payment ? (int)$payment['id'] : null;

        // 1. Insert Refund Record
        $refundNumber = Refund::generateRefundNumber();
        $refundId = Database::insert('refunds', [
            'refund_number' => $refundNumber,
            'order_id'      => $orderId,
            'payment_id'    => $paymentId,
            'amount'        => $refundAmount,
            'currency'      => $order['currency'] ?? 'RWF',
            'reason'        => trim($reason),
            'status'        => 'processed',
            'requested_by'  => $adminUserId,
            'approved_by'   => $adminUserId,
            'processed_at'  => date('Y-m-d H:i:s'),
            'created_at'    => date('Y-m-d H:i:s')
        ]);

        // 2. Update Payment Refunded Amount
        if ($payment) {
            $newRefundedTotal = ((float)$payment['refunded_amount']) + $refundAmount;
            $isFullyRefunded = ($newRefundedTotal >= (float)$payment['amount']);

            Database::update('payments', [
                'refunded_amount' => $newRefundedTotal,
                'status'          => $isFullyRefunded ? 'refunded' : 'partially_refunded',
                'updated_at'      => date('Y-m-d H:i:s')
            ], ['id' => $paymentId]);
        }

        // 3. Update Order Status
        $remainingAfter = $refundableMax - $refundAmount;
        $orderNewStatus = ($remainingAfter <= 0) ? 'refunded' : 'partially_refunded';

        Database::update('orders', [
            'status'         => $orderNewStatus,
            'payment_status' => $orderNewStatus,
            'updated_at'     => date('Y-m-d H:i:s')
        ], ['id' => $orderId]);

        // 4. Record Financial Ledger Debit
        FinancialTransaction::record([
            'order_id'   => $orderId,
            'payment_id' => $paymentId,
            'refund_id'  => $refundId,
            'user_id'    => $order['user_id'],
            'type'       => 'refund',
            'amount'     => $refundAmount,
            'currency'   => $order['currency'] ?? 'RWF',
            'direction'  => 'debit',
            'status'     => 'completed',
            'reference'  => $refundNumber,
            'notes'      => 'Refund for order ' . $order['order_number'] . ': ' . $reason
        ]);

        // 5. Optionally Revoke Course Enrollment Access
        if ($cancelEnrollment) {
            foreach ($order['items'] as $item) {
                if ($item['item_type'] === 'course') {
                    Database::query(
                        "UPDATE enrollments SET status = 'dropped', updated_at = NOW() WHERE user_id = :uid AND course_id = :cid",
                        ['uid' => $order['user_id'], 'cid' => $item['item_id']]
                    );
                }
            }
        }

        // 6. Notification & Audit Log
        Notification::send(
            (int)$order['user_id'],
            'Refund Processed: ' . format_money($refundAmount, $order['currency']),
            'A refund of ' . format_money($refundAmount, $order['currency']) . ' for order ' . $order['order_number'] . ' has been processed. Reason: ' . $reason,
            url('student/orders')
        );

        AuditLog::log('order_refunded', 'order', $orderId, [
            'refund_number' => $refundNumber,
            'amount' => $refundAmount,
            'reason' => $reason
        ]);

        return [
            'success' => true,
            'refund_number' => $refundNumber,
            'message' => "Refund of " . format_money($refundAmount, $order['currency']) . " processed successfully."
        ];
    }

    /**
     * Verify manual bank transfer / offline payment.
     */
    public static function verifyManualPayment(int $orderId, ?int $adminUserId = null): array {
        $order = Order::findWithRelations($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $payment = $order['latest_payment'];
        if (!$payment) {
            return ['success' => false, 'message' => 'Payment record not found for this order.'];
        }

        static::completeOrder($orderId, (int)$payment['id'], 'MANUAL-APPROVAL-BY-ADMIN-' . ($adminUserId ?: 1));

        static::addOrderNote($orderId, 'Manual payment verified by Admin #' . ($adminUserId ?: 1), $adminUserId);

        return ['success' => true, 'message' => 'Manual payment verified and student enrolled successfully!'];
    }

    /**
     * Add internal staff note to an order.
     */
    public static function addOrderNote(int $orderId, string $note, ?int $userId = null, bool $isCustomerVisible = false): int {
        return Database::insert('order_notes', [
            'order_id'            => $orderId,
            'user_id'             => $userId,
            'note'                => trim($note),
            'is_customer_visible' => $isCustomerVisible ? 1 : 0,
            'created_at'          => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Cancel a pending order.
     */
    public static function cancelOrder(int $orderId, string $reason = ''): array {
        $order = Order::findWithRelations($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        if ($order['payment_status'] === 'paid') {
            return ['success' => false, 'message' => 'Paid orders cannot be cancelled directly. Please process a refund instead.'];
        }

        Database::update('orders', [
            'status'         => 'cancelled',
            'payment_status' => 'failed',
            'updated_at'     => date('Y-m-d H:i:s')
        ], ['id' => $orderId]);

        Database::query("UPDATE payments SET status = 'cancelled' WHERE order_id = :oid AND status = 'pending'", ['oid' => $orderId]);

        AuditLog::log('order_cancelled', 'order', $orderId, ['reason' => $reason]);

        return ['success' => true, 'message' => 'Order has been cancelled.'];
    }
}
