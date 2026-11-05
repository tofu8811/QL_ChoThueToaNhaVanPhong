<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Phân quyền vận hành: Nhân sự BQL phụ trách tòa nhà nào.
     */
    public function up(): void
    {
        Schema::create('manager_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('pm_id')->constrained('property_managers')->cascadeOnDelete(); // Nhân sự BQL.
            $table->foreignUuid('building_id')->constrained('buildings')->cascadeOnDelete(); // Tòa nhà quản lý.
            $table->boolean('is_primary')->default(false); // True: Trưởng ban quản lý chịu trách nhiệm chính tại tòa.
            $table->foreignUuid('assigned_by_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Admin phân công.
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['pm_id', 'building_id'], 'uq_manager_building');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_buildings');
    }
};
