<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thư viện ảnh chụp tòa nhà văn phòng (Mặt tiền, Sảnh, Hầm xe,...).
     */
    public function up(): void
    {
        Schema::create('building_images', function (Blueprint $table) {
            $table->id(); // Khóa chính tự tăng.
            $table->foreignUuid('building_id')->constrained('buildings')->cascadeOnDelete(); // Tòa nhà sở hữu hình ảnh.
            $table->string('image_url', 255); // Đường dẫn ảnh trên Cloud Storage.
            $table->boolean('is_primary')->default(false); // True: Ảnh đại diện hiển thị ngoài danh sách tìm kiếm.
            $table->unsignedSmallInteger('display_order')->default(0); // Thứ tự hiển thị trong bộ sưu tập (Slider).
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_images');
    }
};
