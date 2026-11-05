<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thư viện ảnh thực tế bên trong mặt bằng cho thuê.
     */
    public function up(): void
    {
        Schema::create('unit_images', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('unit_id')->constrained('units')->cascadeOnDelete(); // Thuộc về mặt bằng nào.
            $table->string('image_url', 255); // Đường dẫn ảnh trên Cloud Storage.
            $table->boolean('is_primary')->default(false); // Ảnh chính đại diện của phòng.
            $table->unsignedSmallInteger('display_order')->default(0); // Thứ tự hiển thị.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_images');
    }
};
