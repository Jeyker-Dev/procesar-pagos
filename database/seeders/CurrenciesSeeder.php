<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            'usd',
            'eur',
            'gbp',
        ];

        foreach ($currencies as $currency) {
            Currency::create([
                'iso' => $currency,
            ]);
        }
    }
}
