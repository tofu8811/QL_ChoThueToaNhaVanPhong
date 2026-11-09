<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bổ sung các mã số định danh hành chính chuẩn (province_code, district_code, ward_code)
     * vào bảng buildings để tối ưu hóa việc tìm kiếm, phân trang và liên kết với danh mục provinces.
     */
    public function up(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->unsignedInteger('province_code')->nullable()->after('province');
            $table->unsignedInteger('district_code')->nullable()->after('district');
            $table->unsignedInteger('ward_code')->nullable()->after('ward');

            $table->foreign('province_code')
                ->references('code')
                ->on('provinces')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index(['is_published', 'province_code'], 'idx_buildings_published_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropForeign(['province_code']);
            $table->dropIndex('idx_buildings_published_province');
            $table->dropColumn(['province_code', 'district_code', 'ward_code']);
        });
    }
};
