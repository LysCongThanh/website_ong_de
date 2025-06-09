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
        $extensionExists = DB::select("SELECT 1 FROM pg_extension WHERE extname = 'vector'");
        if (empty($extensionExists)) {
            throw new \Exception("The 'vector' extension is not installed. Please ask a database administrator to run: CREATE EXTENSION vector;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
