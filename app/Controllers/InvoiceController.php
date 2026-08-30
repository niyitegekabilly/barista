<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Invoice;
use App\Models\Receipt;

class InvoiceController extends Controller {

    /**
     * Show official Invoice.
     */
    public function show(Request $request, string $invoiceNumber): void {
        $invoice = Invoice::findByInvoiceNumber($invoiceNumber);
        if (!$invoice) {
            $this->abort(404);
            return;
        }

        // Security check: Must be owner or have 'invoices.view' permission
        $user = auth();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        if ((int)$user['id'] !== (int)$invoice['user_id'] && !in_array($user['role_slug'] ?? '', ['admin', 'super_admin'])) {
            $this->abort(403);
            return;
        }

        $this->render('invoices/invoice', [
            'pageTitle' => 'Invoice ' . $invoice['invoice_number'],
            'invoice' => $invoice
        ]);
    }

    /**
     * Show official Payment Receipt.
     */
    public function receipt(Request $request, string $receiptNumber): void {
        $receipt = Receipt::findByReceiptNumber($receiptNumber);
        if (!$receipt) {
            $this->abort(404);
            return;
        }

        $user = auth();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        if ((int)$user['id'] !== (int)$receipt['user_id'] && !in_array($user['role_slug'] ?? '', ['admin', 'super_admin'])) {
            $this->abort(403);
            return;
        }

        $this->render('invoices/receipt', [
            'pageTitle' => 'Payment Receipt ' . $receipt['receipt_number'],
            'receipt' => $receipt
        ]);
    }
}
