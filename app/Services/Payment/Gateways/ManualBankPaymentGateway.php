<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentGatewayInterface;
use App\Models\Payment;

class ManualBankPaymentGateway implements PaymentGatewayInterface {

    public function getIdentifier(): string {
        return 'bank_transfer';
    }

    public function getName(): string {
        return 'Direct Bank Transfer / Offline Invoice (BK, I&M, Equity)';
    }

    public function isEnabled(): bool {
        return true;
    }

    public function initiatePayment(array $order, array $customerData = [], array $options = []): array {
        $txRef = Payment::generateTransactionReference('bank');

        return [
            'success' => true,
            'transaction_reference' => $txRef,
            'gateway' => 'bank_transfer',
            'amount' => (float)$order['final_amount'],
            'currency' => $order['currency'] ?? 'RWF',
            'redirect_url' => url('checkout/success/' . $order['order_number']),
            'message' => 'Please transfer ' . format_money($order['final_amount'], $order['currency']) . ' to Bank of Kigali Account: 00040-0694582-33 (Beyond Barista Academy Ltd) using reference ' . $order['order_number'],
            'provider_data' => [
                'bank_name' => 'Bank of Kigali (BK)',
                'account_name' => 'Beyond Barista Academy Ltd',
                'account_number' => '00040-0694582-33',
                'reference' => $order['order_number'],
                'status' => 'pending_verification'
            ]
        ];
    }

    public function verifyPayment(string $transactionReference, array $payload = []): array {
        return [
            'success' => true,
            'paid' => true,
            'amount' => (float)($payload['amount'] ?? 0),
            'currency' => $payload['currency'] ?? 'RWF',
            'gateway_transaction_id' => 'BANK-VERIFIED-' . date('YmdHis'),
            'message' => 'Manual bank transfer verified by Administrator.',
            'raw_response' => $payload
        ];
    }

    public function processRefund(array $payment, float $amount, string $reason): array {
        return [
            'success' => true,
            'refund_reference' => 'MANUAL-REF-' . date('YmdHis'),
            'message' => 'Manual refund recorded.'
        ];
    }

    public function handleWebhook(array $headers, string $rawBody): array {
        return ['success' => false, 'message' => 'Webhooks not applicable for manual bank transfer.'];
    }
}
