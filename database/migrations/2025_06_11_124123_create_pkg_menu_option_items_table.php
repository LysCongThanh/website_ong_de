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
        Schema::create('pkg_menu_option_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkg_menu_option_id')->constrained('pkg_menu_options')->onDelete('cascade');
            $table->string('name');
            $table->string('unit')->nullable();
            $table->float('quantity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkg_menu_option_items');
    }
};
