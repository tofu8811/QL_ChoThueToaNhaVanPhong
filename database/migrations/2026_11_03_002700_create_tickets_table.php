<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Phiếu yêu cầu hỗ trợ, khiếu nại và sự cố kỹ thuật tòa nhà (Tickets).
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID phiếu hỗ trợ.
            $table->string('ticket_code', 30); // Mã phiếu hỗ trợ (VD: TK-202611-0089).
            $table->foreignUuid('building_id')->constrained('buildings')->restrictOnDelete(); // Tòa nhà xảy ra sự việc.

            // 1. Phân loại và định danh người tạo phiếu (Phương án 1: Explicit Nullable FKs)
            $table->string('creator_role', 20)->default('TENANT'); // TENANT | PM | SALE | ADMIN
            $table->foreignUuid('creator_tenant_id')->nullable()->constrained('tenants')->nullOnDelete(); // Khách thuê tạo
            $table->foreignUuid('creator_pm_id')->nullable()->constrained('property_managers')->nullOnDelete(); // Kỹ thuật / BQL tạo
            $table->foreignUuid('creator_sale_id')->nullable()->constrained('sales')->nullOnDelete(); // Nhân viên Sales tạo
            $table->foreignUuid('creator_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Admin / Ban giám đốc tạo

            // 2. Đối tượng liên quan / mặt bằng phát sinh (cho phép NULL nếu là sự cố khu vực chung tòa nhà)
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->restrictOnDelete(); // Khách thuê chịu ảnh hưởng (NULL nếu sự cố công cộng).
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts')->restrictOnDelete(); // Hợp đồng thuê liên quan.
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->restrictOnDelete(); // Vị trí mặt bằng gặp sự cố (NULL nếu sự cố khu vực chung).
            $table->string('category', 40); // Nhóm yêu cầu: MAINTENANCE (Sửa chữa kỹ thuật) | OVERTIME_CONSTRUCTION (Đăng ký thi công ngoài giờ) | SECURITY_CLEANING (Vệ sinh / An ninh) | SERVICE_COMPLAINT (Khiếu nại dịch vụ).
            $table->string('priority', 20)->default('NORMAL'); // Mức độ khẩn cấp: LOW | NORMAL | HIGH | URGENT.
            $table->string('title', 200); // Tiêu đề ngắn gọn mô tả sự cố.
            $table->text('description'); // Mô tả chi tiết hiện tượng sự cố.
            $table->foreignUuid('assigned_pm_id')->nullable()->constrained('property_managers')->nullOnDelete(); // Nhân viên kỹ thuật ban quản lý được giao phụ trách.
            $table->string('status', 30)->default('NEW'); // Trạng thái xử lý: NEW | IN_PROGRESS | ON_HOLD | WAITING_CONFIRMATION | COMPLETED | CANCELLED (Cast to TicketStatus::class).
            $table->string('on_hold_reason', 500)->nullable(); // Lý do tạm ngưng nếu phải chờ linh kiện thay thế từ nhà sản xuất.
            $table->dateTime('expected_completion_at')->nullable(); // Thời gian cam kết khắc phục xong (Cam kết SLA).
            $table->text('resolution_summary')->nullable(); // Tóm tắt biên bản nghiệm thu sửa chữa kỹ thuật.
            $table->dateTime('resolved_at')->nullable(); // Thời điểm kỹ thuật viên hoàn tất xử lý tại hiện trường.
            $table->dateTime('completed_at')->nullable(); // Thời điểm khách thuê nghiệm thu và đóng phiếu.
            $table->unsignedTinyInteger('tenant_rating')->nullable(); // Đánh giá mức độ hài lòng của khách (1 đến 5 sao).
            $table->string('tenant_feedback', 500)->nullable(); // Ý kiến đóng góp cải thiện chất lượng dịch vụ của khách.
            $table->timestamps();
            $table->softDeletes();

            $table->index('creator_role', 'idx_tickets_creator_role');

            $table->index('status', 'idx_tickets_status');
            $table->index(['ticket_code', 'deleted_at'], 'idx_tickets_code_lookup');
            $table->index(['status', 'resolved_at'], 'idx_tickets_auto_close_72h');
            $table->index(['building_id', 'status', 'priority', 'created_at'], 'idx_tickets_building_status_priority');

            // Composite Foreign Keys chống lệch tòa nhà
            $table->foreign(['building_id', 'tenant_id', 'contract_id'], 'fk_tickets_contract_isolation')
                  ->references(['building_id', 'tenant_id', 'id'])->on('contracts')
                  ->restrictOnDelete();

            $table->foreign(['building_id', 'unit_id'], 'fk_tickets_unit_isolation')
                  ->references(['building_id', 'id'])->on('units')
                  ->restrictOnDelete();
        });

        // Ràng buộc toàn vẹn & Partial Unique Indexes
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            $isMariaDb = false;
            try {
                $version = DB::select('SELECT VERSION() as v')[0]->v ?? '';
                $isMariaDb = stripos($version, 'MariaDB') !== false;
            } catch (\Throwable $e) {}

            if ($isMariaDb) {
                DB::statement("CREATE UNIQUE INDEX `uq_tickets_active_code` ON `tickets` (`ticket_code`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_tickets_active_code` ON `tickets` ((IF(`deleted_at` IS NULL, `ticket_code`, NULL)))");
            }
                        DB::statement("ALTER TABLE `tickets` ADD CONSTRAINT `chk_tickets_creator_role` CHECK (`creator_role` IN ('TENANT', 'PM', 'SALE', 'ADMIN'))");
            DB::statement("ALTER TABLE `tickets` ADD CONSTRAINT `chk_tickets_creator_integrity` CHECK (
                (`creator_role` = 'TENANT' AND `creator_tenant_id` IS NOT NULL) OR
                (`creator_role` = 'PM' AND `creator_pm_id` IS NOT NULL) OR
                (`creator_role` = 'SALE' AND `creator_sale_id` IS NOT NULL) OR
                (`creator_role` = 'ADMIN' AND `creator_admin_id` IS NOT NULL)
            )");

            DB::statement("ALTER TABLE `tickets` ADD CONSTRAINT `chk_tickets_status` CHECK (`status` IN ('NEW', 'IN_PROGRESS', 'ON_HOLD', 'WAITING_CONFIRMATION', 'COMPLETED', 'CANCELLED'))");
            DB::statement("ALTER TABLE `tickets` ADD CONSTRAINT `chk_tickets_category` CHECK (`category` IN ('MAINTENANCE', 'OVERTIME_CONSTRUCTION', 'SECURITY_CLEANING', 'SERVICE_COMPLAINT'))");
            DB::statement("ALTER TABLE `tickets` ADD CONSTRAINT `chk_tickets_priority` CHECK (`priority` IN ('LOW', 'NORMAL', 'HIGH', 'URGENT'))");
            DB::statement("ALTER TABLE `tickets` ADD CONSTRAINT `chk_tickets_rating` CHECK (`tenant_rating` IS NULL OR `tenant_rating` BETWEEN 1 AND 5)");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_tickets_active_code ON tickets (ticket_code) WHERE deleted_at IS NULL");
                        if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_tickets_creator_role CHECK (creator_role IN ('TENANT', 'PM', 'SALE', 'ADMIN'))");
                DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_tickets_creator_integrity CHECK (
                    (creator_role = 'TENANT' AND creator_tenant_id IS NOT NULL) OR
                    (creator_role = 'PM' AND creator_pm_id IS NOT NULL) OR
                    (creator_role = 'SALE' AND creator_sale_id IS NOT NULL) OR
                    (creator_role = 'ADMIN' AND creator_admin_id IS NOT NULL)
                )");
                DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_tickets_status CHECK (status IN ('NEW', 'IN_PROGRESS', 'ON_HOLD', 'WAITING_CONFIRMATION', 'COMPLETED', 'CANCELLED'))");
                DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_tickets_category CHECK (category IN ('MAINTENANCE', 'OVERTIME_CONSTRUCTION', 'SECURITY_CLEANING', 'SERVICE_COMPLAINT'))");
                DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_tickets_priority CHECK (priority IN ('LOW', 'NORMAL', 'HIGH', 'URGENT'))");
                DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_tickets_rating CHECK (tenant_rating IS NULL OR tenant_rating BETWEEN 1 AND 5)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
