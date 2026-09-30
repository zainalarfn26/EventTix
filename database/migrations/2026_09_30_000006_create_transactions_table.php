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
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('order_code')->unique();
            $table->decimal('gross_amount', 12, 2);
            $table->string('payment_type')->nullable();
            $table->string('snap_token')->nullable();
            $table->enum('transaction_status', ['pending', 'settlement', 'deny', 'expire', 'cancel'])->default('pending');
            $table->json('raw_payload')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_code', 'transaction_status']);
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
