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
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'image')) {
                $table->dropColumn('image');
            }
            if (!Schema::hasColumn('product_variants', 'images')) {
                $table->json('images')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'images')) {
                $table->dropColumn('images');
            }
            if (!Schema::hasColumn('product_variants', 'image')) {
                $table->string('image')->nullable()->after('status');
            }
        });
    }
};
