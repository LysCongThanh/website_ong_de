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
        Schema::create('customer_segment_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('priceable_id');
            $table->string('priceable_type');
            $table->foreignId('customer_segment_id')->constrained('customer_segments')->onDelete('cascade')->nullable();
            $table->foreignId('price_type_id')->constrained('price_types')->nullable();
            $table->decimal('price', 12, 0)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['priceable_id', 'priceable_type', 'customer_segment_id'], 'priceable_csp_index');

            $table->index(['priceable_id', 'priceable_type', 'customer_segment_id', 'price_type_id'], 'idx_priceable_segment_type_full');
            $table->index(['priceable_id', 'priceable_type', 'is_active'], 'idx_priceable_segment_active');
            $table->index(['customer_segment_id', 'is_active'], 'idx_segment_active');
            $table->index(['price_type_id', 'is_active'], 'idx_segment_price_type_active');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segment_prices');
    }
};
