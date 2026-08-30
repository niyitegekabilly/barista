<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\FinanceService;

class AdminPaymentController extends Controller {

    /**
     * Payments List with gateway and status filters.
     */
    public function index(Request $request): void {
        $filters = [
            'q'       => $request->input('q', ''),
            'status'  => $request->input('status', 'all'),
            'gateway' => $request->input('gateway', 'all')
        ];

        $page = (int)$request->input('page', 1);
        $paymentsData = FinanceService::getPayments($filters, $page, 20);

        $this->render('admin/payments/index', [
            'pageTitle' => 'Payment Transactions',
            'payments' => $paymentsData['data'],
            'pagination' => $paymentsData,
            'filters' => $filters
        ], 'dashboard');
    }
}
