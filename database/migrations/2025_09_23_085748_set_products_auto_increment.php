<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1000;');
    }

    public function down(): void
    {
        // Revert back if necessary (not recommended in production)
        // DB::statement('ALTER TABLE products AUTO_INCREMENT = 1;');
    }
};
