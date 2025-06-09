<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('embedding_vectors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('embeddable_id');
            $table->string('embeddable_type');
            $table->longText('vector');
            $table->timestamps();

            $table->index(['embeddable_id', 'embeddable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embedding_vectors');
    }
};
