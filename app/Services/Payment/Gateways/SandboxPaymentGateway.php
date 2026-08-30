<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentGatewayInterface;
use App\Models\Payment;

class SandboxPaymentGateway implements PaymentGatewayInterface {

    public function getIdentifier(): string {
        return 'sandbox';
    }

    public function getName(): string {
        return 'Instant Test Sandbox (Simulated Payment)';
    }

    public function isEnabled(): bool {
        return true;
    }

    public function initiatePayment(array $order, array $customerData = [], array $options = []): array {
        $txRef = Payment::generateTransactionReference('sandbox');

        return [
            'success' => true,
            'transaction_reference' => $txRef,
            'gateway' => 'sandbox',
            'amount' => (float)$order['final_amount'],
            'currency' => $order['currency'] ?? 'RWF',
            'redirect_url' => url('checkout/success/' . $order['order_number']),
            'message' => 'Sandbox simulated payment successful.',
            'provider_data' => [
                'mode' => 'sandbox_instant',
                'status' => 'simulated_success'
            ]
        ];
    }

    public function verifyPayment(string $transactionReference, array $payload = []): array {
        return [
            'success' => true,
            'paid' => true,
            'amount' => (float)($payload['amount'] ?? 0),
            'currency' => $payload['currency'] ?? 'RWF',
            'gateway_transaction_id' => 'SANDBOX-TX-' . strtoupper(bin2hex(random_bytes(4))),
            'message' => 'Sandbox payment automatically verified.',
            'raw_response' => $payload
        ];
    }

    public function processRefund(array $payment, float $amount, string $reason): array {
        return [
            'success' => true,
            'refund_reference' => 'SANDBOX-REF-' . strtoupper(bin2hex(random_bytes(4))),
            'message' => 'Sandbox refund simulation completed.'
        ];
    }

    public function handleWebhook(array $headers, string $rawBody): array {
        $data = json_decode($rawBody, true) ?: [];
        return [
            'success' => true,
            'transaction_reference' => $data['transaction_reference'] ?? '',
            'paid' => true,
            'amount' => (float)($data['amount'] ?? 0),
            'currency' => $data['currency'] ?? 'RWF',
            'gateway_transaction_id' => 'SANDBOX-WB-' . bin2hex(random_bytes(4)),
            'raw' => $data
        ];
    }
}
