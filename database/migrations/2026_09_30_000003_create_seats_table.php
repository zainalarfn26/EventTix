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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('seat_number'); // e.g. A-12, VVIP-01
            $table->string('row'); // e.g. A, B, C
            $table->integer('column'); // e.g. 1, 2, 3
            $table->enum('category', ['VVIP', 'VIP', 'REGULAR'])->default('REGULAR');
            $table->decimal('base_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['venue_id', 'seat_number']);
            $table->index(['venue_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
