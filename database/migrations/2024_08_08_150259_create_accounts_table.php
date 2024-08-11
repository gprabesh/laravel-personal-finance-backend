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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('current_balance', 11, 2)->default(0);
            $table->char('current_balance_type', 2)->default('DR');
            $table->tinyInteger('needs_balance_recalculation')->default(0)->index();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('account_group_id')->constrained('account_groups');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
