<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->decimal('debit', 11, 2)->default(0);
            $table->decimal('credit', 11, 2)->default(0);
            $table->foreignId('account_id')->constrained('accounts');
            $table->decimal('account_balance', 11, 2)->default(0);
            $table->char('account_balance_type', 2)->default('DR')->index();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
