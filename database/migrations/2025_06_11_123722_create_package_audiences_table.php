<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('package_audiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('audience_id')->constrained('audiences')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['package_id', 'audience_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_audiences');
    }
};
