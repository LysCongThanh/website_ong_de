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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->enum('type', ['individual', 'travel'])->default('individual');
            $table->text('summary')->nullable();
            $table->text('content')->nullable();
            $table->string('duration')->nullable();
            $table->integer('min_quantity')->default(1);
            $table->boolean('is_featured')->default(false);
            $table->string('slug')->unique();
            $table->json('keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->datetime('available_start')->nullable();
            $table->datetime('available_end')->nullable();
            $table->text('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('last_updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['available_start', 'available_end']);
            $table->index('is_featured');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
