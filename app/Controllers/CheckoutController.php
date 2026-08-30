<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PaymentService;
use App\Models\MembershipPlan;

class CheckoutController extends Controller
{
    private PaymentService $paymentService;

    public function __construct()
    {
        parent::__construct();
        $this->paymentService = new PaymentService();
    }

    public function show(\App\Core\Request $request, string $slug): void
    {
        $course = $this->db()->fetchOne(
            "SELECT c.*, u.name instructor_name FROM courses c JOIN users u ON c.created_by = u.id WHERE c.slug = ? AND c.is_published = 1",
            [$slug]
        );

        if (!$course) {
            $this->abort(404);
            return;
        }

        $userId = auth()['id'];

        // Already enrolled?
        $enrolled = $this->db()->fetchOne("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $course['id']]);
        if ($enrolled) {
            $this->redirect('/student/classroom/' . $slug);
            return;
        }

        // Free course → enroll directly
        if ($course['price'] == 0) {
            $this->enrollFree($userId, $course['id']);
            $this->flash('success', 'You have been enrolled in ' . $course['title'] . '!');
            $this->redirect('/student/classroom/' . $slug);
            return;
        }

        $this->render('public/checkout', compact('course'), 'main');
    }

    /**
     * Show membership plan checkout page
     * Expects query parameters: type=membership, id=<plan_id>
     */
    public function showMembership(\App\Core\Request $request): void
    {
        $type = $request->input('type');
        $id = $request->input('id');
        if ($type !== 'membership' || empty($id)) {
            $this->abort(404);
            return;
        }
        $plan = MembershipPlan::find($id);
        if (!$plan) {
            $this->abort(404);
            return;
        }
        $item = $plan;
        $itemTitle = $plan['title'] ?? $plan['name'] ?? 'Membership';
        $itemType = 'membership';
        $originalPrice = $plan['price'] ?? 0;
        $finalAmount = $originalPrice;
        $discountAmount = 0;
        $couponCode = $request->input('coupon') ?? '';
        $this->render('public/checkout', compact('item', 'itemTitle', 'itemType', 'originalPrice', 'finalAmount', 'discountAmount', 'couponCode'), 'main');
    }

    public function applyCoupon(): void
    {
        $code   = strtoupper(trim($this->request->input('code')));
        $amount = (float)$this->request->input('amount');

        $coupon = $this->db()->fetchOne(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at >= CURDATE())
             AND (max_uses = 0 OR uses_count < max_uses)",
            [$code]
        );

        if (!$coupon) {
            $this->json(['success' => false, 'message' => 'Invalid or expired coupon code.']);
            return;
        }

        $discount = $coupon['discount_type'] === 'percentage'
            ? round($amount * ($coupon['discount_value'] / 100))
            : min($coupon['discount_value'], $amount);

        $this->json([
            'success'   => true,
            'discount'  => $discount,
            'final'     => max(0, $amount - $discount),
            'coupon_id' => $coupon['id'],
        ]);
    }

    public function initiate(): void
    {
        $userId   = auth()['id'];
        $courseId = (int)$this->request->input('course_id');
        $method   = $this->request->input('payment_method', 'momo');
        $phone    = $this->request->input('phone');
        $couponId = $this->request->input('coupon_id');

        $result = $this->paymentService->initiatePayment([
            'user_id'   => $userId,
            'course_id' => $courseId,
            'method'    => $method,
            'phone'     => $phone,
            'coupon_id' => $couponId,
        ]);

        if ($result['success']) {
            $this->json(['success' => true, 'message' => 'Payment initiated. Please confirm on your phone.', 'order_id' => $result['order_id']]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']]);
        }
    }

    public function callback(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->paymentService->handleCallback($data);
        http_response_code(200);
        exit;
    }

    private function enrollFree(int $userId, int $courseId): void
    {
        // Check if already enrolled
        $enrolled = $this->db()->fetchOne(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?",
            [$userId, $courseId]
        );

        if (!$enrolled) {
            $this->db()->insert('enrollments', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'payment_status' => 'free',
                'enrollment_date' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
