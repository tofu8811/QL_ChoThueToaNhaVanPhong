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
        Schema::table('accountants', function (Blueprint $table) {
            if (!Schema::hasColumn('accountants', 'can_approve_adjustment')) {
                $table->boolean('can_approve_adjustment')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accountants', function (Blueprint $table) {
            if (Schema::hasColumn('accountants', 'can_approve_adjustment')) {
                $table->dropColumn('can_approve_adjustment');
            }
        });
    }
};
