<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng danh mục Tỉnh / Thành phố trực thuộc Trung ương (Provinces).
     * Dữ liệu chuẩn hóa theo 34 tỉnh/thành phố sau sáp nhập (Nghị quyết 202/2025/QH15).
     */
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            // Mã tỉnh chuẩn từ Tổng cục Thống kê / API (VD: 79: TP.HCM, 1: Hà Nội, 75: Đồng Nai)
            $table->unsignedInteger('code')->primary();
            $table->string('name', 100); // Tên đầy đủ: Thành phố Hồ Chí Minh, Tỉnh Đồng Nai,...
            $table->string('slug', 120)->unique(); // URL thân thiện phục vụ bộ lọc SEO portal: ho-chi-minh
            $table->string('division_type', 50)->nullable(); // thành phố trung ương / tỉnh
            $table->string('codename', 100)->nullable(); // Định danh mã code dạng chữ: ho_chi_minh
            $table->unsignedSmallInteger('phone_code')->nullable(); // Mã vùng điện thoại
            $table->timestamps();

            $table->index('name', 'idx_provinces_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
