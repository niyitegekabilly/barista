<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Order;

class InvoiceService {

    /**
     * Generate or fetch Invoice for an order.
     */
    public static function createOrGetInvoice(int $orderId): array {
        $existing = Database::fetchOne("SELECT * FROM invoices WHERE order_id = :oid LIMIT 1", ['oid' => $orderId]);
        if ($existing) {
            return Invoice::findByInvoiceNumber($existing['invoice_number']);
        }

        $order = Order::findWithRelations($orderId);
        if (!$order) {
            throw new \RuntimeException("Order not found: {$orderId}");
        }

        $invoiceNumber = Invoice::generateInvoiceNumber();
        $isPaid = ($order['payment_status'] === 'paid');

        $invoiceId = Database::insert('invoices', [
            'invoice_number' => $invoiceNumber,
            'order_id'       => $orderId,
            'user_id'        => $order['user_id'],
            'subtotal'       => $order['subtotal_amount'] ?? $order['total_amount'],
            'discount'       => $order['discount_amount'] ?? 0.00,
            'tax'            => $order['tax_amount'] ?? 0.00,
            'total'          => $order['final_amount'],
            'currency'       => $order['currency'] ?? 'RWF',
            'status'         => $isPaid ? 'paid' : 'issued',
            'billing_info'   => json_encode([
                'name'    => $order['billing_name'] ?? ($order['user']['name'] ?? ''),
                'email'   => $order['billing_email'] ?? ($order['user']['email'] ?? ''),
                'phone'   => $order['billing_phone'] ?? '',
                'address' => $order['billing_address'] ?? ''
            ]),
            'due_date'       => date('Y-m-d', strtotime('+7 days')),
            'paid_at'        => $isPaid ? date('Y-m-d H:i:s') : null,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ]);

        return Invoice::findByInvoiceNumber($invoiceNumber);
    }

    /**
     * Generate or fetch Receipt for a paid payment.
     */
    public static function createOrGetReceipt(int $paymentId): array {
        $existing = Database::fetchOne("SELECT * FROM receipts WHERE payment_id = :pid LIMIT 1", ['pid' => $paymentId]);
        if ($existing) {
            return Receipt::findByReceiptNumber($existing['receipt_number']);
        }

        $payment = Database::fetchOne("SELECT * FROM payments WHERE id = :id", ['id' => $paymentId]);
        if (!$payment) {
            throw new \RuntimeException("Payment not found: {$paymentId}");
        }

        $order = Order::findWithRelations((int)$payment['order_id']);
        $receiptNumber = Receipt::generateReceiptNumber();

        $receiptId = Database::insert('receipts', [
            'receipt_number'        => $receiptNumber,
            'order_id'              => $payment['order_id'],
            'payment_id'            => $paymentId,
            'user_id'               => $payment['user_id'],
            'amount'                => $payment['amount'],
            'currency'              => $payment['currency'] ?? 'RWF',
            'payment_method'        => $payment['payment_method'] ?? 'momo',
            'transaction_reference' => $payment['transaction_reference'],
            'created_at'            => date('Y-m-d H:i:s')
        ]);

        return Receipt::findByReceiptNumber($receiptNumber);
    }
}
