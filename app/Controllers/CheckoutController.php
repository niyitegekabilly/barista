<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Course;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\Payment\PaymentGatewayManager;

class CheckoutController extends Controller {

    /**
     * Public Student Checkout Page.
     */
    public function show(Request $request, string $slug): void {
        $user = auth();
        if (!$user) {
            $this->flash('warning', 'Please sign in or create an account to enroll in this course.');
            $this->redirect('login?redirect=' . urlencode('checkout/' . $slug));
            return;
        }

        $course = Course::findBySlug($slug);
        if (!$course || empty($course['is_published'])) {
            $this->abort(404);
            return;
        }

        $gateways = PaymentGatewayManager::getAvailableGateways();

        $this->render('public/checkout/index', [
            'pageTitle' => 'Checkout — ' . $course['title'],
            'course' => $course,
            'user' => $user,
            'gateways' => $gateways
        ]);
    }

    /**
     * AJAX Validate Coupon Code.
     */
    public function validateCoupon(Request $request): void {
        $code = $request->input('code', '');
        $amount = (float)$request->input('amount', 0);
        $courseId = (int)$request->input('course_id');
        $userId = auth_id();

        $result = Coupon::findValid($code, $amount, $courseId, $userId);
        Response::json($result);
    }

    /**
     * Process Checkout & Initiate Payment.
     */
    public function process(Request $request): void {
        $user = auth();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        $courseId = (int)$request->input('course_id');
        $couponCode = $request->input('coupon_code');
        $paymentMethod = $request->input('payment_method', 'momo');

        $billingData = [
            'name'           => $request->input('billing_name', $user['name']),
            'email'          => $request->input('billing_email', $user['email']),
            'phone'          => $request->input('billing_phone', ''),
            'address'        => $request->input('billing_address', ''),
            'customer_notes' => $request->input('customer_notes', '')
        ];

        $res = OrderService::createCourseOrder((int)$user['id'], $courseId, $billingData, $couponCode, $paymentMethod);

        if (!$res['success']) {
            $this->flash('danger', $res['message']);
            $this->redirect('courses');
            return;
        }

        if ($request->isAjax()) {
            Response::json($res);
        }

        $this->redirect($res['redirect_url']);
    }

    /**
     * Order Success Confirmation Page.
     */
    public function success(Request $request, string $orderNumber): void {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order) {
            $this->abort(404);
            return;
        }

        $this->render('public/checkout/success', [
            'pageTitle' => 'Order Confirmed — Beyond Barista Academy',
            'order' => $order
        ]);
    }

    /**
     * Order Failed / Payment Retry Page.
     */
    public function failed(Request $request, string $orderNumber): void {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order) {
            $this->abort(404);
            return;
        }

        $this->render('public/checkout/failed', [
            'pageTitle' => 'Payment Pending / Failed — Beyond Barista Academy',
            'order' => $order
        ]);
    }
}
