<?php

namespace App\Http\Controllers;

use App\Resolvers\PaymentPlatformResolver;
use App\Services\PaypalService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected ?PaymentPlatformResolver $paymentPlatformResolver = null;

    public function __construct(PaymentPlatformResolver $paymentPlatformResolver)
    {
        $this->paymentPlatformResolver = $paymentPlatformResolver;
    }

    public function approval()
    {
        if (session()->has('paymentPlatformId')) {
            $paymentPlatform = $this->paymentPlatformResolver->resolveService(session()->get('paymentPlatformId'));

            return $paymentPlatform->handleApproval();
        }

        return redirect()->route('dashboard')->with('error','We cannot retrieve your payment platform. Try again, please.');
    }

    public function cancelled()
    {
        return redirect()->route('dashboard')->with('error','You cancelled the payment.');
    }
}
