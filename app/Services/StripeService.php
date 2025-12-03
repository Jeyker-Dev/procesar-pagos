<?php

namespace App\Services;

use App\ConsumeExternalServices;

class StripeService
{
    use ConsumeExternalServices;

    protected string $baseUri = '';

    protected string $key = '';

    protected string $secret = '';

    public function __construct()
    {
        $this->baseUri = config('services.stripe.base_uri');
        $this->key = config('services.stripe.key');
        $this->secret = config('services.stripe.secret');
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers): void
    {
        $headers['Authorization'] = $this->resolveAccessToken();
    }

    public function resolveAccessToken(): string
    {
        return "Bearer {$this->secret}";
    }

    public function handlePayment(array $request)
    {
        $intent = $this->createIntent(
            $request['amount'],
            $request['currency'],
            $request['paymentMethod'],
        );

        $intent = json_decode($intent);

        session()->put('paymentIntentId', $intent->id);

        return redirect()->route('approval');
    }

    public function handleApproval()
    {
        if (session()->has('paymentIntentId')) {
            $paymentIntentId = session()->get('paymentIntentId');

            $current = $this->retrievePaymentIntent($paymentIntentId);
            $current = json_decode($current);

            if ($current->status !== 'requires_confirmation') {
                return redirect()->route('dashboard')->with('error', 'The payment intent is not in a valid state for confirmation.');
            }

            $intent = $this->confirmPaymentIntent($paymentIntentId);

            $intent = json_decode($intent);

            if ($intent->status === 'requires_action') {
                $clientSecret = $intent->client_secret;

                return view('stripe.3d-secure')->with('clientSecret', $clientSecret);
            }

            if ( $intent->status == 'succeeded') {
               $currency = strtoupper($intent->currency);
               $amount = $intent->amount / $this->resolveFactor($currency);

               return redirect()->route('dashboard')->with('success', "Thanks, we have received your payment of {$amount} {$currency}.");
            }
        }

        return redirect()->route('dashboard')->with('error', 'We are unable to retrieve your payment intent. Please try again.');
    }

    public function createIntent($value, $currency, $paymentMethod)
    {
        return $this->makeRequest(
            'POST',
            'v1/payment_intents',
            [],
            [
                'amount' => $value * $this->resolveFactor($currency),
                'currency' => strtolower($currency),
                'payment_method' => $paymentMethod,
                'payment_method_types' => ['card'],
                'confirmation_method' => 'manual',
            ],
        );
    }

    public function confirmPaymentIntent($paymentIntentId)
    {
        return $this->makeRequest(
            'POST',
            "v1/payment_intents/{$paymentIntentId}/confirm",
        );
    }

    public function retrievePaymentIntent($paymentIntentId)
    {
        return $this->makeRequest(
            'GET',
            "v1/payment_intents/{$paymentIntentId}",
        );
    }

    public function resolveFactor($currency): int
    {
        $zeroDecimalCurrencies = [
            'JPY',
        ];

        if (in_array(strtoupper($currency), $zeroDecimalCurrencies)) {
            return 1;
        }

        return 100;
    }
}
