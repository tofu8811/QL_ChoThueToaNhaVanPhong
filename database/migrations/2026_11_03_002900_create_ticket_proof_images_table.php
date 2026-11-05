<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Hình ảnh bằng chứng sự cố và hình ảnh nghiệm thu sau sửa chữa (Ticket Proof Images).
     */
    public function up(): void
    {
        Schema::create('ticket_proof_images', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('ticket_id')->constrained('tickets')->cascadeOnDelete(); // Phiếu hỗ trợ sở hữu hình ảnh.
            $table->foreignId('ticket_history_id')->nullable()->constrained('ticket_history')->nullOnDelete(); // Đính kèm trong bước cập nhật tiến độ nào.
            $table->string('image_url', 255); // Đường dẫn ảnh hiện trường (Supabase Storage).
            $table->string('proof_type', 30)->default('INCIDENT'); // Loại ảnh: INCIDENT (Ảnh chụp khi xảy ra sự cố) | COMPLETION (Ảnh sau khi sửa chữa xong) | LOG_ATTACHMENT (Tài liệu đính kèm).
            $table->string('caption', 255)->nullable(); // Chú thích hiện trường.
            $table->string('uploaded_by_actor_type', 20); // Người đăng tải ảnh: TENANT | PM | ADMIN.
            $table->timestamp('created_at')->useCurrent();
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `ticket_proof_images` ADD CONSTRAINT `chk_tpi_proof_type` CHECK (`proof_type` IN ('INCIDENT', 'COMPLETION', 'LOG_ATTACHMENT'))");
            DB::statement("ALTER TABLE `ticket_proof_images` ADD CONSTRAINT `chk_tpi_uploader` CHECK (`uploaded_by_actor_type` IN ('TENANT', 'PM', 'ADMIN'))");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ticket_proof_images ADD CONSTRAINT chk_tpi_proof_type CHECK (proof_type IN ('INCIDENT', 'COMPLETION', 'LOG_ATTACHMENT'))");
            DB::statement("ALTER TABLE ticket_proof_images ADD CONSTRAINT chk_tpi_uploader CHECK (uploaded_by_actor_type IN ('TENANT', 'PM', 'ADMIN'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_proof_images');
    }
};
