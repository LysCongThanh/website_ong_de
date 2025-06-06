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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id');
            $table->string('entity_type', 50);
            $table->string('locale', 10);
            $table->json('translations');
            $table->timestamps();

            $table->foreign('locale')
                ->references('code')
                ->on('languages')
                ->onDelete('restrict');

            $table->index(['entity_id', 'entity_type'], 'translations_entity_index');
            $table->index('locale', 'translations_locale_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
