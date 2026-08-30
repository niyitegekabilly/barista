<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\Payment\PaymentGatewayManager;

class WebhookController extends Controller {

    /**
     * Public Webhook Receiver: /api/webhooks/payment/{gateway}
     */
    public function handle(Request $request, string $gateway): void {
        $gw = PaymentGatewayManager::get($gateway);
        if (!$gw) {
            Response::json(['success' => false, 'message' => 'Unknown gateway'], 404);
        }

        $rawBody = file_get_contents('php://input') ?: '';
        $headers = getallheaders() ?: [];

        // 1. Parse webhook payload
        $result = $gw->handleWebhook($headers, $rawBody);
        $txRef = $result['transaction_reference'] ?? '';

        if (empty($txRef)) {
            Response::json(['success' => false, 'message' => 'Missing transaction reference'], 400);
        }

        // 2. Idempotency Check via payment_webhook_events
        $eventId = md5($gateway . '_' . $txRef . '_' . $rawBody);
        $existingEvent = Database::fetchOne(
            "SELECT * FROM payment_webhook_events WHERE gateway = :gw AND event_id = :eid",
            ['gw' => $gateway, 'eid' => $eventId]
        );

        if ($existingEvent && $existingEvent['status'] === 'processed') {
            Response::json(['success' => true, 'message' => 'Event already processed']);
        }

        // Record received event
        if (!$existingEvent) {
            Database::insert('payment_webhook_events', [
                'gateway'      => $gateway,
                'event_id'     => $eventId,
                'event_type'   => 'payment_notification',
                'payload'      => $rawBody,
                'status'       => 'received',
                'created_at'   => date('Y-m-d H:i:s')
            ]);
        }

        // 3. Find payment record
        $payment = Payment::findByTransactionReference($txRef);
        if (!$payment) {
            Response::json(['success' => false, 'message' => 'Payment transaction reference not found'], 404);
        }

        // 4. Verify and complete order if paid
        if (!empty($result['paid']) && $payment['status'] !== 'successful') {
            OrderService::completeOrder(
                (int)$payment['order_id'],
                (int)$payment['id'],
                $result['gateway_transaction_id'] ?? $txRef,
                $result['raw'] ?? []
            );

            Database::query(
                "UPDATE payment_webhook_events SET status = 'processed', processed_at = NOW() WHERE gateway = :gw AND event_id = :eid",
                ['gw' => $gateway, 'eid' => $eventId]
            );
        }

        Response::json(['success' => true, 'message' => 'Webhook received and processed']);
    }
}
