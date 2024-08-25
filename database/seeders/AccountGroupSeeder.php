<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccountGroup::updateOrCreate([
            'id' => 1,
            'name' => 'Income',
            'code' => 'INC',
        ]);
        AccountGroup::updateOrCreate([
            'id' => 2,
            'name' => 'Expense',
            'code' => 'EXP',
        ]);
        AccountGroup::updateOrCreate([
            'id' => 3,
            'name' => 'Wallets',
            'code' => 'WLT',
        ]);
        AccountGroup::updateOrCreate([
            'id' => 4,
            'name' => 'System',
            'code' => 'SYS',
        ]);
    }
}
