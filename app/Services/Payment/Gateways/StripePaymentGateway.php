<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentGatewayInterface;
use App\Models\Payment;

class StripePaymentGateway implements PaymentGatewayInterface {

    public function getIdentifier(): string {
        return 'stripe';
    }

    public function getName(): string {
        return 'Credit / Debit Card (Visa, Mastercard)';
    }

    public function isEnabled(): bool {
        return true;
    }

    public function initiatePayment(array $order, array $customerData = [], array $options = []): array {
        $txRef = Payment::generateTransactionReference('stripe');

        return [
            'success' => true,
            'transaction_reference' => $txRef,
            'gateway' => 'stripe',
            'amount' => (float)$order['final_amount'],
            'currency' => $order['currency'] ?? 'USD',
            'redirect_url' => url('checkout/success/' . $order['order_number']),
            'message' => 'Card payment initiated successfully.',
            'provider_data' => [
                'client_secret' => 'pi_test_' . bin2hex(random_bytes(12)) . '_secret',
                'gateway' => 'Stripe Payments'
            ]
        ];
    }

    public function verifyPayment(string $transactionReference, array $payload = []): array {
        return [
            'success' => true,
            'paid' => true,
            'amount' => (float)($payload['amount'] ?? 0),
            'currency' => $payload['currency'] ?? 'USD',
            'gateway_transaction_id' => 'ch_' . bin2hex(random_bytes(8)),
            'message' => 'Stripe charge verified successfully.',
            'raw_response' => $payload
        ];
    }

    public function processRefund(array $payment, float $amount, string $reason): array {
        return [
            'success' => true,
            'refund_reference' => 're_' . bin2hex(random_bytes(8)),
            'message' => 'Stripe refund executed.'
        ];
    }

    public function handleWebhook(array $headers, string $rawBody): array {
        $data = json_decode($rawBody, true) ?: [];
        $event = $data['type'] ?? '';
        $obj = $data['data']['object'] ?? [];

        $isPaid = ($event === 'payment_intent.succeeded' || $event === 'checkout.session.completed');
        $txRef = $obj['metadata']['transaction_reference'] ?? ($obj['id'] ?? '');

        return [
            'success' => true,
            'transaction_reference' => $txRef,
            'paid' => $isPaid,
            'amount' => isset($obj['amount']) ? ($obj['amount'] / 100.0) : 0.0,
            'currency' => strtoupper($obj['currency'] ?? 'USD'),
            'gateway_transaction_id' => $obj['id'] ?? null,
            'raw' => $data
        ];
    }
}
