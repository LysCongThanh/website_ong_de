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
        Schema::create('customer_capacity_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('priceable_id');
            $table->string('priceable_type');
            $table->foreignId('price_type_id')->constrained('price_types')->nullable();
            $table->foreignId('customer_segment_id')->constrained('customer_segments')->nullable();
            $table->unsignedInteger('min_person')->nullable();
            $table->unsignedInteger('max_person')->nullable();
            $table->decimal('price', 12, 0)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['priceable_id', 'priceable_type'], 'idx_capacity_priceable');
            $table->index(['priceable_id', 'priceable_type', 'price_type_id'], 'idx_capacity_priceable_type');
            $table->index(['priceable_id', 'priceable_type', 'is_active'], 'idx_capacity_priceable_active');
            $table->index(['min_person', 'max_person'], 'idx_capacity_range');
            $table->index(['price_type_id', 'is_active'], 'idx_capacity_price_type_active');
            $table->index('deleted_at');

            $table->index(['priceable_id', 'priceable_type', 'min_person', 'max_person', 'is_active'], 'idx_capacity_lookup_full');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_capacity_prices');
    }
};
