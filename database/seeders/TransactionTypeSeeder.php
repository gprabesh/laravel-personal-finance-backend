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
        TransactionType::create([
            'name' => 'Receipt',
            'code' => 'R',
        ]);
        TransactionType::create([
            'name' => 'Payment',
            'code' => 'P',
        ]);
        TransactionType::create([
            'name' => 'Transfer',
            'code' => 'TF',
        ]);
        TransactionType::create([
            'name' => 'Debt',
            'code' => 'DB',
        ]);
        TransactionType::create([
            'name' => 'Credit',
            'code' => 'CR',
        ]);
        TransactionType::create([
            'name' => 'Debt Paid',
            'code' => 'DBP',
        ]);
        TransactionType::create([
            'name' => 'Credit Received',
            'code' => 'CRR',
        ]);
    }
}
