<?php

use Livewire\Attributes\{Layout};
use Livewire\Volt\Component;
use App\Models\Currency;
use App\Models\PaymentPlatform;
use App\Services\PaypalService;
use App\Resolvers\PaymentPlatformResolver;
use App\Services\StripeService;

new
#[Layout('layouts.app')]
class extends Component {
    public ?float $amount = null;
    public ?int $paymentPlatform = null;
    public string $currency = "";
    public ?string $paymentMethod = null;
    protected ?PaymentPlatformResolver $paymentPlatformResolver = null;

    public function boot(PaymentPlatformResolver $paymentPlatformResolver): void
    {
        $this->paymentPlatformResolver = $paymentPlatformResolver;
    }

    public function with(): array
    {
        return [
            'currencies' => Currency::all(),
            'paymentPlatforms' => PaymentPlatform::all(),
        ];
    }

    public function pay(): void
    {
        $validated = $this->validate([
            'amount' => 'required|numeric',
            'currency' => 'required',
            'paymentPlatform' => 'required|exists:payment_platforms,id',
            'paymentMethod' => 'nullable|string',
        ]);

        $paymentPlatform = $this->paymentPlatformResolver->resolveService($validated['paymentPlatform']);

        session()->put('paymentPlatformId', $validated['paymentPlatform']);

        $paymentPlatform->handlePayment($validated);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @if(session()->has('success'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2">
            {{ session('error') }}
        </div>
    @endif

    <div
        x-data='{
        selectedPlatform: $wire.entangle("paymentPlatform"),
        names: @json($paymentPlatforms->pluck("name", "id")),
    }'
        class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow-sm w-6/12 mx-auto">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50">
                            <h2 class="">Make a Payment</h2>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <form id="payment-form" wire:submit.prevent="pay" class="p-6">

                                <flux:text class="my-2 text-black">How Much Do You Want to Pay?</flux:text>
                                <flux:input.group class="mb-4">
                                    <flux:select wire:model="currency" class="max-w-fit">
                                        <flux:select.option value="">Select a coin</flux:select.option>
                                        @foreach($currencies as $currency)
                                            <flux:select.option>{{ strtoupper( $currency->iso) }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:input wire:model="amount" class="" placeholder="$99.99"/>
                                </flux:input.group>

                                <flux:radio.group wire:model="paymentPlatform"
                                                  label="Select the desire payment platform" variant="cards"
                                                  class="max-sm:flex-col">
                                    @foreach($paymentPlatforms as $paymentPlatform)
                                        <flux:radio class="" value="{{ $paymentPlatform->id }}">
                                            <flux:radio.indicator/>

                                            <div class="flex-1">
                                                <img src="{{ asset($paymentPlatform->image) }}" alt="Platform Image"
                                                     class="object-cover object-center">
                                            </div>
                                        </flux:radio>
                                    @endforeach
                                </flux:radio.group>

                                <div>
                                    <template x-if="selectedPlatform">
                                        <div>
                                            <template x-if="names[selectedPlatform] == 'PayPal'">
                                                <div>
                                                    <p>
                                                        PayPal will redirect you to their site to complete the payment.
                                                    </p>
                                                </div>
                                            </template>

                                            <template x-if="names[selectedPlatform] == 'Stripe'">
                                                <div class="mt-5">
                                                    <x-panel.stripe-payment/>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <flux:button id="pay-button" class="my-4" type="submit" variant="primary">Pay</flux:button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
