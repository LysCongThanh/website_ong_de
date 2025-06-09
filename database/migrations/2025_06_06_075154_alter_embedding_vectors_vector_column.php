<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE embedding_vectors ALTER COLUMN vector TYPE vector(1536) USING vector::vector');

        DB::statement('CREATE INDEX embeddable_vector_idx ON embedding_vectors USING ivfflat (vector vector_cosine_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS embeddable_vector_idx');

        DB::statement('ALTER TABLE embedding_vectors ALTER COLUMN vector TYPE text');
    }
};
