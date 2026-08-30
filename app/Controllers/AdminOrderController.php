<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\FinanceService;

class AdminOrderController extends Controller {

    /**
     * Orders Management List with advanced filters.
     */
    public function index(Request $request): void {
        $filters = [
            'q'              => $request->input('q', ''),
            'status'         => $request->input('status', 'all'),
            'payment_status' => $request->input('payment_status', 'all'),
            'payment_method' => $request->input('payment_method', 'all'),
            'start_date'     => $request->input('start_date', ''),
            'end_date'       => $request->input('end_date', '')
        ];

        $page = (int)$request->input('page', 1);
        $ordersData = FinanceService::getOrders($filters, $page, 20);
        $kpis = FinanceService::getDashboardKpis();

        $this->render('admin/orders/index', [
            'pageTitle' => 'Orders Management',
            'orders' => $ordersData['data'],
            'pagination' => $ordersData,
            'kpis' => $kpis,
            'filters' => $filters
        ], 'dashboard');
    }

    /**
     * 360° Order Detail Page.
     */
    public function show(Request $request, int $id): void {
        $order = Order::findWithRelations($id);
        if (!$order) {
            $this->flash('danger', 'Order not found.');
            $this->redirect('admin/orders');
            return;
        }

        $this->render('admin/orders/show', [
            'pageTitle' => 'Order #' . $order['order_number'],
            'order' => $order
        ], 'dashboard');
    }

    /**
     * Process Full or Partial Refund.
     */
    public function refund(Request $request, int $id): void {
        $amount = (float)$request->input('amount');
        $reason = $request->input('reason', 'Customer requested refund');
        $cancelEnrollment = !empty($request->input('cancel_enrollment'));
        $adminId = auth_id();

        $res = OrderService::processRefund($id, $amount, $reason, $adminId, $cancelEnrollment);

        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/orders/' . $id);
    }

    /**
     * Verify manual bank transfer / offline payment.
     */
    public function verifyManualPayment(Request $request, int $id): void {
        $adminId = auth_id();
        $res = OrderService::verifyManualPayment($id, $adminId);

        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/orders/' . $id);
    }

    /**
     * Add staff note to order.
     */
    public function addNote(Request $request, int $id): void {
        $note = $request->input('note');
        $isVisible = !empty($request->input('is_customer_visible'));

        if (!empty(trim($note))) {
            OrderService::addOrderNote($id, $note, auth_id(), $isVisible);
            $this->flash('success', 'Internal note added to order.');
        }

        $this->redirect('admin/orders/' . $id);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, int $id): void {
        $reason = $request->input('reason', 'Cancelled by administrator');
        $res = OrderService::cancelOrder($id, $reason);

        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/orders/' . $id);
    }

    /**
     * Export Orders to CSV.
     */
    public function export(Request $request): void {
        $csv = FinanceService::exportOrdersCsv($request->all());
        $filename = 'bba_orders_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csv;
        exit;
    }
}
