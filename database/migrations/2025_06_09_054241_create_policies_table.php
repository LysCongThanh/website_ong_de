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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('type')->nullable()->index();
            $table->string('policyable_type')->index();
            $table->unsignedBigInteger('policyable_id')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['policyable_type', 'policyable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
