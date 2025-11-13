<?php

use Livewire\Attributes\{Layout};
use Livewire\Volt\Component;
use App\Models\Currency;
use App\Models\PaymentPlatform;

new
#[Layout('layouts.app')]
class extends Component {
    public object $currencies;
    public object $paymentPlatforms;
    public int $amount;

    public function mount(): void
    {
        $this->paymentPlatforms = PaymentPlatform::all();
        $this->currencies = Currency::all();
    }

    public function pay(): void
    {
        $validated = $this->validate([
            'amount' => 'required|numeric',
            'currencies' => 'required',
            'paymentPlatforms' => 'required|exists:payment_platforms,id',
        ]);

        dd($validated);
    }
}; ?>

<div x-data="{ selectedPlatform: @entangle('selectedPlatform').live }">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow-sm w-6/12 mx-auto">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50">
                            <h2 class="">Make a Payment</h2>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <form wire:submit.prevent="pay" class="p-6">

                                <flux:text class="my-2 text-black">How Much Do You Want to Pay?</flux:text>
                                <flux:input.group class="mb-4">
                                    <flux:select wire:model="currencies" class="max-w-fit">
                                        @foreach($currencies as $currency)
                                            <flux:select.option>{{ strtoupper( $currency->iso) }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:input wire:model="amount" class="" placeholder="$99.99"/>
                                </flux:input.group>

                                <flux:radio.group wire:model="paymentPlatforms" label="Select the desire payment platform" variant="cards" class="max-sm:flex-col">
                                    @foreach($paymentPlatforms as $paymentPlatform)
                                        <flux:radio class="" value="{{ $paymentPlatform->id }}">
                                            <flux:radio.indicator />

                                            <div class="flex-1">
                                                <img src="{{ asset($paymentPlatform->image) }}" alt="Platform Image" class="object-cover object-center">
                                            </div>
                                        </flux:radio>
                                    @endforeach
                                </flux:radio.group>

                                <flux:button class="my-4" type="submit" variant="primary">Pay</flux:button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
