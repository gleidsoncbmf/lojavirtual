<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Store;

class PaymentService
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    private array $gateways = [];

    public function registerGateway(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getName()] = $gateway;
    }

    public function getGateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new \InvalidArgumentException("Payment gateway [{$name}] is not registered.");
        }

        return $this->gateways[$name];
    }

    public function getAvailableGateways(Store $store): array
    {
        $config = $store->payment_config ?? [];
        $available = [];

        foreach ($this->gateways as $name => $gateway) {
            if (isset($config[$name]) && !empty($config[$name]['enabled'])) {
                $available[] = $name;
            }
        }

        // Always add 'whatsapp' (close with seller) option
        $available[] = 'whatsapp';

        return $available;
    }

    public function hasGateway(string $name): bool
    {
        return isset($this->gateways[$name]);
    }
}
