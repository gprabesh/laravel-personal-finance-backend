<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransactionType::updateOrCreate([
            'id' => 1,
            'name' => 'Receipt',
            'code' => 'R',
        ]);
        TransactionType::updateOrCreate([
            'id' => 2,
            'name' => 'Payment',
            'code' => 'P',
        ]);
        TransactionType::updateOrCreate([
            'id' => 3,
            'name' => 'Transfer',
            'code' => 'TF',
        ]);
        TransactionType::updateOrCreate([
            'id' => 4,
            'name' => 'Debt',
            'code' => 'DB',
        ]);
        TransactionType::updateOrCreate([
            'id' => 5,
            'name' => 'Credit',
            'code' => 'CR',
        ]);
        TransactionType::updateOrCreate([
            'id' => 6,
            'name' => 'Debt Paid',
            'code' => 'DBP',
        ]);
        TransactionType::updateOrCreate([
            'id' => 7,
            'name' => 'Credit Received',
            'code' => 'CRR',
        ]);
        TransactionType::updateOrCreate([
            'id' => 8,
            'name' => 'Opening Balance',
            'code' => 'OB',
        ]);
    }
}
