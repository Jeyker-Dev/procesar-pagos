<?php

namespace App\Http\Controllers;

use App\Services\PaypalService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function approval()
    {
        $paymentPlatform = resolve(PaypalService::class);

        return $paymentPlatform->handleApproval();
    }

    public function cancelled()
    {

    }
}
