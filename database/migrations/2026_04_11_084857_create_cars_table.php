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
    Schema::create('cars', function (Blueprint $table) {
        $table->id();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('model');
        $table->year('year');
        $table->string('plate_number')->unique();
        $table->string('color');
        $table->enum('transmission', ['automatic', 'manual']);
        $table->enum('fuel_type', ['gasoline', 'diesel', 'hybrid', 'electric']);
        $table->unsignedTinyInteger('seats');
        $table->unsignedTinyInteger('doors');
        $table->unsignedTinyInteger('bags_capacity')->nullable();
        $table->decimal('daily_rate', 10, 2);
        $table->decimal('weekly_rate', 10, 2)->nullable();
        $table->decimal('monthly_rate', 10, 2)->nullable();
        $table->integer('mileage_limit_per_day')->nullable();
        $table->text('description')->nullable();
        $table->string('main_image')->nullable();
        $table->boolean('is_available')->default(true);
        $table->boolean('is_featured')->default(false);
        $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
