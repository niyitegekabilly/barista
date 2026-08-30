<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Coupon;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Course;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Core\Database;

class PaymentService {
    public static function processCheckout(int $userId, string $itemType, int $itemId, ?string $couponCode = null, string $paymentMethod = 'sandbox'): array {
        $itemTitle = '';
        $price = 0.00;

        if ($itemType === 'course') {
            $course = Course::find($itemId);
            if (!$course) throw new \RuntimeException("Course not found.");
            $itemTitle = $course['title'];
            $price = (float) ($course['discount_price'] ?: $course['price']);
        } elseif ($itemType === 'membership') {
            $plan = MembershipPlan::find($itemId);
            if (!$plan) throw new \RuntimeException("Plan not found.");
            $itemTitle = $plan['name'] . ' (' . ucfirst($plan['billing_interval']) . ')';
            $price = (float) $plan['price'];
        }

        $discount = 0.00;
        $couponId = null;

        if ($couponCode) {
            $coupon = Coupon::findValid($couponCode, $price);
            if ($coupon) {
                $couponId = (int) $coupon['id'];
                if ($coupon['discount_type'] === 'percentage') {
                    $discount = round(($price * (float)$coupon['discount_value']) / 100, 2);
                } else {
                    $discount = min((float)$coupon['discount_value'], $price);
                }
            }
        }

        $finalAmount = max(0.00, $price - $discount);
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

        $orderId = 0;
        $paymentId = 0;

        Database::transaction(function() use (&$orderId, &$paymentId, $userId, $orderNumber, $price, $discount, $finalAmount, $couponId, $itemType, $itemId, $itemTitle, $paymentMethod) {
            $orderId = Database::insert('orders', [
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'total_amount' => $price,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
                'currency' => config('payments.currency', 'RWF'),
                'status' => 'completed', // In demo/sandbox mode mark as completed immediately
                'coupon_id' => $couponId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Database::insert('order_items', [
                'order_id' => $orderId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'item_title' => $itemTitle,
                'price' => $price,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $txRef = 'TX-' . date('YmdHis') . '-' . rand(1000, 9999);
            $paymentId = Database::insert('payments', [
                'order_id' => $orderId,
                'user_id' => $userId,
                'payment_method' => $paymentMethod,
                'transaction_reference' => $txRef,
                'amount' => $finalAmount,
                'currency' => config('payments.currency', 'RWF'),
                'status' => 'successful',
                'provider_response' => json_encode(['mock' => true, 'timestamp' => time()]),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update coupon usage
            if ($couponId) {
                Database::query("UPDATE coupons SET uses_count = uses_count + 1 WHERE id = :cid", ['cid' => $couponId]);
            }

            // Fulfill order
            if ($itemType === 'course') {
                CourseService::enroll($userId, $itemId);
            } elseif ($itemType === 'membership') {
                $plan = MembershipPlan::find($itemId);
                $days = ($plan['billing_interval'] === 'year') ? 365 : 30;
                Database::insert('memberships', [
                    'user_id' => $userId,
                    'plan_id' => $itemId,
                    'status' => 'active',
                    'start_date' => date('Y-m-d'),
                    'end_date' => date('Y-m-d', strtotime("+{$days} days")),
                    'payment_reference' => $txRef,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        });

        Notification::send(
            $userId,
            'Payment Confirmed & Access Granted',
            "Thank you! Your payment for {$itemTitle} has been verified.",
            url($itemType === 'course' ? "student/courses" : "student/dashboard")
        );

        AuditLog::log('order_completed', 'order', $orderId, ['amount' => $finalAmount, 'item' => $itemTitle]);

        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'amount' => $finalAmount
        ];
    }
}
