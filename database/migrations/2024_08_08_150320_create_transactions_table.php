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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamp('transaction_date')->useCurrent()->index();
            $table->decimal('amount', 11, 2)->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('transaction_type_id')->constrained('transaction_types');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('parent_id')->nullable()->constrained('transactions')->onDelete('SET NULL');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
