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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'options')) {
                $table->json('options')->nullable()->after('description');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            // Drop old columns if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('product_variants', 'variant_name')) $columnsToDrop[] = 'variant_name';
            if (Schema::hasColumn('product_variants', 'variant_value')) $columnsToDrop[] = 'variant_value';
            if (Schema::hasColumn('product_variants', 'additional_price')) $columnsToDrop[] = 'additional_price';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            // Add new columns
            if (!Schema::hasColumn('product_variants', 'attributes')) {
                $table->json('attributes')->after('product_id');
            }
            if (!Schema::hasColumn('product_variants', 'sku')) {
                $table->string('sku')->unique()->nullable()->after('attributes');
            }
            if (!Schema::hasColumn('product_variants', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('sku');
            }
            if (!Schema::hasColumn('product_variants', 'stock')) {
                $table->integer('stock')->default(0)->after('price');
            }
            if (!Schema::hasColumn('product_variants', 'status')) {
                $table->boolean('status')->default(true)->after('stock');
            }
            if (!Schema::hasColumn('product_variants', 'image')) {
                $table->string('image')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['attributes', 'sku', 'price', 'stock', 'status', 'image']);
            $table->string('variant_name');
            $table->string('variant_value');
            $table->decimal('additional_price', 10, 2)->default(0.00);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
