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
        Schema::create('base_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('priceable_id');
            $table->string('priceable_type');
            $table->foreignId('price_type_id')->constrained('price_types')->nullable();
            $table->decimal('price', 12, 0)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['priceable_id', 'priceable_type'], 'priceable_index');

            $table->index(['priceable_id', 'priceable_type', 'price_type_id'], 'idx_priceable_type_composite');
            $table->index(['priceable_id', 'priceable_type', 'is_active'], 'idx_priceable_active');
            $table->index(['price_type_id', 'is_active'], 'idx_price_type_active');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_prices');
    }
};
