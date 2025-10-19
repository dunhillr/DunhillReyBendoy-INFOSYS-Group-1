<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Will be set to start at 1000 after creation

            $table->string('name');

            $table->decimal('net_weight', 8, 2)->nullable();
            $table->foreignId('net_weight_unit_id')->nullable()->constrained('units');

            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(0);

            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();

            // Composite unique key for product uniqueness
            $table->unique(['name', 'net_weight', 'net_weight_unit_id', 'category_id'], 'unique_product_combo');
        });

        // Set starting ID to 1000
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1000;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
