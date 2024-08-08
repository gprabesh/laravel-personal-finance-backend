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
        AccountGroup::create([
            'name' => 'Income',
            'code' => 'INC',
        ]);
        AccountGroup::create([
            'name' => 'Expense',
            'code' => 'EXP',
        ]);
        AccountGroup::create([
            'name' => 'Assets',
            'code' => 'AST',
        ]);
        AccountGroup::create([
            'name' => 'System',
            'code' => 'SYS',
        ]);
    }
}
