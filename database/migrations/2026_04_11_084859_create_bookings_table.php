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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('car_id')->constrained()->cascadeOnDelete();
        $table->foreignId('pickup_branch_id')->constrained('branches')->cascadeOnDelete();
        $table->foreignId('dropoff_branch_id')->constrained('branches')->cascadeOnDelete();

        $table->date('start_date');
        $table->date('end_date');
        $table->time('pickup_time');
        $table->time('dropoff_time');
        $table->integer('total_days');

        $table->decimal('daily_rate', 10, 2);
        $table->decimal('subtotal', 10, 2);
        $table->decimal('extras_total', 10, 2)->default(0);
        $table->decimal('tax_amount', 10, 2)->default(0);
        $table->decimal('discount_amount', 10, 2)->default(0);
        $table->decimal('total_amount', 10, 2);

        $table->enum('status', ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled'])->default('pending');
        $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'partial'])->default('unpaid');

        $table->text('notes')->nullable();
        $table->string('driver_name');
        $table->string('driver_phone');
        $table->string('driver_license_number');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
