<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Lịch sử luân chuyển trạng thái và trao đổi tin nhắn trên phiếu hỗ trợ (Ticket History).
     */
    public function up(): void
    {
        Schema::create('ticket_history', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('ticket_id')->constrained('tickets')->cascadeOnDelete(); // Phiếu hỗ trợ cha.
            $table->string('actor_type', 20); // Đối tượng thực hiện hành động: TENANT | PM | ADMIN | SYSTEM.
            $table->foreignUuid('tenant_actor_id')->nullable()->constrained('tenants')->nullOnDelete(); // Khách thuê nhắn phản hồi.
            $table->foreignUuid('pm_actor_id')->nullable()->constrained('property_managers')->nullOnDelete(); // Nhân viên kỹ thuật cập nhật tiến độ.
            $table->foreignUuid('sales_actor_id')->nullable()->constrained('sales')->nullOnDelete(); // Nhân viên Sales trao đổi / phối hợp.
            $table->foreignUuid('admin_actor_id')->nullable()->constrained('admins')->nullOnDelete(); // Ban giám đốc / Admin chỉ đạo.
            $table->string('from_status', 30)->nullable(); // Trạng thái trước khi chuyển (Cast to TicketStatus::class).
            $table->string('to_status', 30); // Trạng thái sau khi chuyển (Cast to TicketStatus::class).
            $table->text('message'); // Nội dung trao đổi hoặc ghi chú kỹ thuật.
            $table->timestamp('created_at')->useCurrent(); // Thời điểm cập nhật tiến trình.

            $table->index('from_status', 'idx_th_from_status');
            $table->index('to_status', 'idx_th_to_status');
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
                        DB::statement("ALTER TABLE `ticket_history` ADD CONSTRAINT `chk_th_actor_type` CHECK (`actor_type` IN ('TENANT', 'PM', 'SALE', 'ADMIN', 'SYSTEM'))");
            DB::statement("ALTER TABLE `ticket_history` ADD CONSTRAINT `chk_th_actor_integrity` CHECK (
                (`actor_type` = 'TENANT' AND `tenant_actor_id` IS NOT NULL) OR
                (`actor_type` = 'PM' AND `pm_actor_id` IS NOT NULL) OR
                (`actor_type` = 'SALE' AND `sales_actor_id` IS NOT NULL) OR
                (`actor_type` = 'ADMIN' AND `admin_actor_id` IS NOT NULL) OR
                (`actor_type` = 'SYSTEM')
            )");
            DB::statement("ALTER TABLE `ticket_history` ADD CONSTRAINT `chk_th_from_status` CHECK (`from_status` IS NULL OR `from_status` IN ('NEW', 'IN_PROGRESS', 'ON_HOLD', 'WAITING_CONFIRMATION', 'COMPLETED', 'CANCELLED'))");
            DB::statement("ALTER TABLE `ticket_history` ADD CONSTRAINT `chk_th_to_status` CHECK (`to_status` IN ('NEW', 'IN_PROGRESS', 'ON_HOLD', 'WAITING_CONFIRMATION', 'COMPLETED', 'CANCELLED'))");
        } elseif ($driver === 'pgsql') {
                        DB::statement("ALTER TABLE ticket_history ADD CONSTRAINT chk_th_actor_type CHECK (actor_type IN ('TENANT', 'PM', 'SALE', 'ADMIN', 'SYSTEM'))");
            DB::statement("ALTER TABLE ticket_history ADD CONSTRAINT chk_th_actor_integrity CHECK (
                (actor_type = 'TENANT' AND tenant_actor_id IS NOT NULL) OR
                (actor_type = 'PM' AND pm_actor_id IS NOT NULL) OR
                (actor_type = 'SALE' AND sales_actor_id IS NOT NULL) OR
                (actor_type = 'ADMIN' AND admin_actor_id IS NOT NULL) OR
                (actor_type = 'SYSTEM')
            )");
            DB::statement("ALTER TABLE ticket_history ADD CONSTRAINT chk_th_from_status CHECK (from_status IS NULL OR from_status IN ('NEW', 'IN_PROGRESS', 'ON_HOLD', 'WAITING_CONFIRMATION', 'COMPLETED', 'CANCELLED'))");
            DB::statement("ALTER TABLE ticket_history ADD CONSTRAINT chk_th_to_status CHECK (to_status IN ('NEW', 'IN_PROGRESS', 'ON_HOLD', 'WAITING_CONFIRMATION', 'COMPLETED', 'CANCELLED'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_history');
    }
};
