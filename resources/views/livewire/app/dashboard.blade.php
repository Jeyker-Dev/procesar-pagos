<?php

use Livewire\Attributes\{Layout};
use Livewire\Volt\Component;
use App\Models\Currency;
use App\Models\User;

new
#[Layout('layouts.app')]
class extends Component {
    public array $currencies;

    public function mount(): void
    {
        $currencies_all = Currency::all();

        foreach ($currencies_all as $currency) {
            $currencies = collect([
                'name' => $currency->iso,
            ]);

            $this->currencies[] = $currencies;
        }
    }
}; ?>

<div>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <x-mary-header title="Make a Payment" size="text-xl" separator />
                        <x-mary-form wire:submit="save">
                            <x-mary-select label="Currency" icon="o-currency-dollar" placeholder="Select a Currency" :options="$currencies" wire:model="currencies" />

                            <x-mary-input label="Amount" wire:model="amount" money hint="It submits an unmasked value" />

                            <x-slot:actions>
                                <x-mary-button label="Pay" class="btn-primary px-10" type="submit" />
                            </x-slot:actions>
                        </x-mary-form>
                    </div>
                </div>
            </div>
        </div>

</div>
