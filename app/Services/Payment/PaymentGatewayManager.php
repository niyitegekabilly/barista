<?php

namespace App\Services\Payment;

use App\Services\Payment\Gateways\MomoPaymentGateway;
use App\Services\Payment\Gateways\StripePaymentGateway;
use App\Services\Payment\Gateways\ManualBankPaymentGateway;
use App\Services\Payment\Gateways\SandboxPaymentGateway;

class PaymentGatewayManager {

    /** @var array<string, PaymentGatewayInterface> */
    protected static array $gateways = [];

    public static function boot(): void {
        if (empty(self::$gateways)) {
            self::register(new MomoPaymentGateway());
            self::register(new StripePaymentGateway());
            self::register(new ManualBankPaymentGateway());
            self::register(new SandboxPaymentGateway());
        }
    }

    public static function register(PaymentGatewayInterface $gateway): void {
        self::$gateways[$gateway->getIdentifier()] = $gateway;
    }

    public static function get(string $identifier): ?PaymentGatewayInterface {
        self::boot();
        return self::$gateways[strtolower(trim($identifier))] ?? null;
    }

    public static function getAvailableGateways(): array {
        self::boot();
        $list = [];
        foreach (self::$gateways as $id => $gateway) {
            if ($gateway->isEnabled()) {
                $list[$id] = [
                    'id' => $gateway->getIdentifier(),
                    'name' => $gateway->getName(),
                ];
            }
        }
        return $list;
    }
}
