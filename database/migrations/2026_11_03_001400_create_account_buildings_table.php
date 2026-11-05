<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Phân quyền kế toán phụ trách công nợ theo tòa nhà.
     */
    public function up(): void
    {
        Schema::create('account_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('accountant_id')->constrained('accountants')->cascadeOnDelete(); // Kế toán viên.
            $table->foreignUuid('building_id')->constrained('buildings')->cascadeOnDelete(); // Tòa nhà phụ trách sổ sách.
            $table->foreignUuid('assigned_by_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Admin phân công.
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['accountant_id', 'building_id'], 'uq_account_building');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_buildings');
    }
};
