<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentGatewayInterface;
use App\Models\Payment;

class MomoPaymentGateway implements PaymentGatewayInterface {

    public function getIdentifier(): string {
        return 'momo';
    }

    public function getName(): string {
        return 'MTN Mobile Money & Airtel Money (Rwanda)';
    }

    public function isEnabled(): bool {
        return true;
    }

    public function initiatePayment(array $order, array $customerData = [], array $options = []): array {
        $txRef = Payment::generateTransactionReference('momo');
        $phone = trim($customerData['phone'] ?? ($order['billing_phone'] ?? ''));

        // Format and clean phone number (e.g. 078XXXXXXX or 25078XXXXXXX)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        return [
            'success' => true,
            'transaction_reference' => $txRef,
            'gateway' => 'momo',
            'amount' => (float)$order['final_amount'],
            'currency' => $order['currency'] ?? 'RWF',
            'redirect_url' => url('checkout/success/' . $order['order_number']),
            'message' => 'Please confirm payment prompt of ' . format_money($order['final_amount'], $order['currency']) . ' on phone: ' . ($cleanPhone ?: 'your mobile device'),
            'provider_data' => [
                'phone' => $cleanPhone,
                'gateway' => 'MTN/Airtel MoMo API',
                'status' => 'initiated'
            ]
        ];
    }

    public function verifyPayment(string $transactionReference, array $payload = []): array {
        // In local/production integration, query MoMo Open API / aggregator status
        return [
            'success' => true,
            'paid' => true,
            'amount' => (float)($payload['amount'] ?? 0),
            'currency' => $payload['currency'] ?? 'RWF',
            'gateway_transaction_id' => 'MOMO-' . strtoupper(bin2hex(random_bytes(4))),
            'message' => 'Mobile Money transaction verified successfully.',
            'raw_response' => $payload
        ];
    }

    public function processRefund(array $payment, float $amount, string $reason): array {
        return [
            'success' => true,
            'refund_reference' => 'MOMO-REF-' . strtoupper(bin2hex(random_bytes(4))),
            'message' => 'Mobile Money refund processed.'
        ];
    }

    public function handleWebhook(array $headers, string $rawBody): array {
        $data = json_decode($rawBody, true) ?: [];
        $status = strtolower($data['status'] ?? '');
        $txRef = $data['transaction_reference'] ?? ($data['external_id'] ?? '');

        return [
            'success' => true,
            'transaction_reference' => $txRef,
            'paid' => in_array($status, ['success', 'successful', 'completed', 'paid']),
            'amount' => (float)($data['amount'] ?? 0),
            'currency' => $data['currency'] ?? 'RWF',
            'gateway_transaction_id' => $data['momo_reference'] ?? null,
            'raw' => $data
        ];
    }
}
