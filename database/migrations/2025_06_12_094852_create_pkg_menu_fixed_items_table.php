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
        Schema::create('pkg_menu_fixed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_menu_id')->constrained('pkg_menus')->onDelete('cascade');
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
        Schema::dropIfExists('pkg_menu_fixed_items');
    }
};
