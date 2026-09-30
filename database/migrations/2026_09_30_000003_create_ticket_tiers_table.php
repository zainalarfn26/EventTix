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
        Schema::create('ticket_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // VVIP, VIP, REGULAR, CAT 1, etc.
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quota');
            $table->unsignedInteger('sold_count')->default(0);
            $table->string('color', 7)->default('#6366f1')->comment('Hex color for zone display');
            $table->string('zone_label')->nullable()->comment('e.g. Zona Depan Panggung');
            $table->text('description')->nullable();
            $table->string('wristband_color')->nullable()->comment('Wristband color for check-in exchange');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_tiers');
    }
};
