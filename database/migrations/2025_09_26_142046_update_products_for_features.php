<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop outdated columns
            if (Schema::hasColumn('products', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('products', 'quantity')) {
                $table->dropColumn('quantity');
            }

            // Add correct columns and constraints
            if (!Schema::hasColumn('products', 'net_weight')) {
                $table->decimal('net_weight', 8, 2)->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'net_weight_unit_id')) {
                $table->foreignId('net_weight_unit_id')->nullable()->constrained('units')->after('net_weight');
            }
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null')->after('price');
            }

            // Add the composite unique key
            $table->unique(['name', 'net_weight', 'net_weight_unit_id', 'category_id'], 'unique_product_combo');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop constraints and columns in reverse order
            $table->dropUnique('unique_product_combo');
            $table->dropForeign(['category_id']);
            $table->dropForeign(['net_weight_unit_id']);
            $table->dropColumn(['net_weight_unit_id', 'net_weight']);

            // Add back old columns for rollback
            $table->integer('quantity')->default(0)->after('price');
            $table->text('description')->nullable()->after('name');
        });
    }
};
