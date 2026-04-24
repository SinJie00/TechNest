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
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                // Drop foreign key first. We wrap in try-catch or just check if we can?
                // Schema builder doesn't have hasForeignKey.
                // We'll rely on correct naming or ignore if it fails?
                // Better: just attempt dropColumn, if it fails due to FK, we know. 
                // But we can try dropping the FK first explicitly.
                try {
                    $table->dropForeign(['parent_id']);
                } catch (\Exception $e) {
                    // FK might not exist or verify failed, proceed to drop column
                }
                $table->dropColumn('parent_id');
            }

            if (Schema::hasColumn('categories', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
             if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
             }
             if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable();
             }
        });
    }
};
