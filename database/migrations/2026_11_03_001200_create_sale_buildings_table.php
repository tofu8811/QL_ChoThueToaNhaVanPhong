<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Phân quyền phụ trách kinh doanh: Chuyên viên Sales được gán bán tòa nhà nào.
     */
    public function up(): void
    {
        Schema::create('sale_buildings', function (Blueprint $table) {
            $table->id(); // Khóa chính tự tăng.
            $table->foreignUuid('sales_id')->constrained('sales')->cascadeOnDelete(); // Chuyên viên kinh doanh được gán.
            $table->foreignUuid('building_id')->constrained('buildings')->cascadeOnDelete(); // Tòa nhà được phân quyền phụ trách.
            $table->boolean('is_active')->default(true); // Trạng thái đang hiệu lực phân công.
            $table->foreignUuid('assigned_by_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Quản trị viên chỉ định.
            $table->timestamp('assigned_at')->useCurrent(); // Thời điểm phân công.

            $table->unique(['sales_id', 'building_id'], 'uq_sale_building');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_buildings');
    }
};
