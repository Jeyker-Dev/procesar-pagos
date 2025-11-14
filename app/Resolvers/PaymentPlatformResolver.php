<?php

namespace App\Resolvers;

use App\Models\PaymentPlatform;
use Illuminate\Support\Collection;

class PaymentPlatformResolver
{
    protected ?Collection $paymentPlatform = null;

    public function __construct()
    {
        $this->paymentPlatform = PaymentPlatform::all();
    }

    public function resolveService(int $paymentPlatformId)
    {
        $platform = $this->paymentPlatform->firstWhere('id', $paymentPlatformId);

        if (!$platform) {
            throw new \InvalidArgumentException("Payment platform id {$paymentPlatformId} not found.");
        }

        $name = strtolower($platform->name);
        $service = config("services.{$name}.class");

        if ($service) {
            return resolve($service);
        }

        throw new \RuntimeException("The selected payment platform \[{$name}\] is not configured in 'config/services.php'.");
    }
}
