<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface {
    /**
     * Get unique identifier string for this gateway.
     */
    public function getIdentifier(): string;

    /**
     * Get human-readable display name.
     */
    public function getName(): string;

    /**
     * Check if gateway is currently enabled.
     */
    public function isEnabled(): bool;

    /**
     * Initiate payment transaction for an order.
     * Returns array with: ['success' => bool, 'transaction_reference' => string, 'redirect_url' => ?string, 'message' => ?string, 'provider_data' => array]
     */
    public function initiatePayment(array $order, array $customerData = [], array $options = []): array;

    /**
     * Verify payment status server-side.
     * Returns array with: ['success' => bool, 'paid' => bool, 'amount' => float, 'currency' => string, 'gateway_transaction_id' => ?string, 'message' => ?string, 'raw_response' => array]
     */
    public function verifyPayment(string $transactionReference, array $payload = []): array;

    /**
     * Process refund where supported by gateway.
     */
    public function processRefund(array $payment, float $amount, string $reason): array;

    /**
     * Handle incoming asynchronous webhook.
     */
    public function handleWebhook(array $headers, string $rawBody): array;
}
