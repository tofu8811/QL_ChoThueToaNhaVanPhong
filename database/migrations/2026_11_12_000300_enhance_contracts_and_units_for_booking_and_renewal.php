<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nâng cấp CSDL phục vụ Hợp đồng Giữ chỗ (Booking), Gia hạn (Renew), Phân định cọc thanh lý và Phân quyền duyệt/hủy.
     */
    public function up(): void
    {
        // 1. Thêm các cột mới vào bảng contracts
        Schema::table('contracts', function (Blueprint $table) {
            $table->date('signed_date')->nullable()->change();
            $table->string('contract_pdf_private_path', 255)->nullable()->change();

            // Khóa ngoại tự tham chiếu hợp đồng cha khi gia hạn chu kỳ mới
            $table->foreignUuid('parent_contract_id')->nullable()->after('id')->constrained('contracts')->nullOnDelete();

            // Nhân viên Sales lập hợp đồng / giữ chỗ
            $table->foreignUuid('created_by_sales_id')->nullable()->after('tenant_id')->constrained('sales')->nullOnDelete();

            // Người phê duyệt ký kích hoạt hợp đồng (Admin hoặc Property Manager)
            $table->foreignUuid('approved_by_admin_id')->nullable()->after('handover_minutes_private_path')->constrained('admins')->nullOnDelete();
            $table->foreignUuid('approved_by_pm_id')->nullable()->after('approved_by_admin_id')->constrained('property_managers')->nullOnDelete();

            // Người phê duyệt nghiệm thu thanh lý hợp đồng (Admin hoặc Property Manager)
            $table->foreignUuid('terminated_by_admin_id')->nullable()->after('termination_note')->constrained('admins')->nullOnDelete();
            $table->foreignUuid('terminated_by_pm_id')->nullable()->after('terminated_by_admin_id')->constrained('property_managers')->nullOnDelete();

            // Thông số nghiệp vụ Giữ chỗ (Booking / Reservation)
            $table->decimal('booking_deposit_amount', 18, 2)->nullable()->default(0.00)->after('deposit_amount');
            $table->dateTime('reservation_expires_at')->nullable()->after('booking_deposit_amount');

            // Phân định tiền hoàn trả và tiền phạt khấu trừ khi thanh lý hợp đồng
            $table->decimal('refund_deposit_amount', 18, 2)->nullable()->default(0.00)->after('terminated_at');
            $table->decimal('penalty_deposit_amount', 18, 2)->nullable()->default(0.00)->after('refund_deposit_amount');

            // Chỉ mục tối ưu hóa truy vấn
            $table->index('parent_contract_id', 'idx_contracts_parent_id');
            $table->index('created_by_sales_id', 'idx_contracts_created_by_sales');
            $table->index('reservation_expires_at', 'idx_contracts_reservation_expires_at');
            $table->index(['status', 'reservation_expires_at'], 'idx_contracts_reservation_expiry');
        });

        // 2. Cập nhật CHECK constraints cho MySQL / PgSQL
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            $isMariaDb = false;
            try {
                $version = DB::select('SELECT VERSION() as v')[0]->v ?? '';
                $isMariaDb = stripos($version, 'MariaDB') !== false;
            } catch (\Throwable $e) {}

            // Thả check constraints cũ nếu có
            try {
                if ($isMariaDb) {
                    DB::statement("ALTER TABLE `contracts` DROP CONSTRAINT `chk_contracts_status`");
                    DB::statement("ALTER TABLE `units` DROP CONSTRAINT `chk_units_status`");
                } else {
                    DB::statement("ALTER TABLE `contracts` DROP CHECK `chk_contracts_status`");
                    DB::statement("ALTER TABLE `units` DROP CHECK `chk_units_status`");
                }
            } catch (\Throwable $e) {}

            // Tạo mới check constraints mở rộng
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_status` CHECK (`status` IN ('DRAFT', 'ACTIVE', 'EXPIRING_SOON', 'EXPIRED', 'TERMINATED', 'RESERVED', 'RENEWED'))");
            DB::statement("ALTER TABLE `units` ADD CONSTRAINT `chk_units_status` CHECK (`status` IN ('AVAILABLE', 'EXPIRING_SOON', 'LEASED', 'MAINTENANCE', 'RESERVED'))");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_deposit_split` CHECK (`refund_deposit_amount` >= 0 AND `penalty_deposit_amount` >= 0 AND `booking_deposit_amount` >= 0)");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_approved_actors` CHECK (`approved_by_admin_id` IS NULL OR `approved_by_pm_id` IS NULL)");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_terminated_actors` CHECK (`terminated_by_admin_id` IS NULL OR `terminated_by_pm_id` IS NULL)");
        } elseif ($driver === 'pgsql') {
            try {
                DB::statement("ALTER TABLE contracts DROP CONSTRAINT IF EXISTS chk_contracts_status");
                DB::statement("ALTER TABLE units DROP CONSTRAINT IF EXISTS chk_units_status");
            } catch (\Throwable $e) {}

            DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_status CHECK (status IN ('DRAFT', 'ACTIVE', 'EXPIRING_SOON', 'EXPIRED', 'TERMINATED', 'RESERVED', 'RENEWED'))");
            DB::statement("ALTER TABLE units ADD CONSTRAINT chk_units_status CHECK (status IN ('AVAILABLE', 'EXPIRING_SOON', 'LEASED', 'MAINTENANCE', 'RESERVED'))");
            DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_deposit_split CHECK (refund_deposit_amount >= 0 AND penalty_deposit_amount >= 0 AND booking_deposit_amount >= 0)");
            DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_approved_actors CHECK (approved_by_admin_id IS NULL OR approved_by_pm_id IS NULL)");
            DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_terminated_actors CHECK (terminated_by_admin_id IS NULL OR terminated_by_pm_id IS NULL)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['parent_contract_id']);
            $table->dropForeign(['created_by_sales_id']);
            $table->dropForeign(['approved_by_admin_id']);
            $table->dropForeign(['approved_by_pm_id']);
            $table->dropForeign(['terminated_by_admin_id']);
            $table->dropForeign(['terminated_by_pm_id']);

            $table->dropIndex('idx_contracts_parent_id');
            $table->dropIndex('idx_contracts_created_by_sales');
            $table->dropIndex('idx_contracts_reservation_expires_at');
            $table->dropIndex('idx_contracts_reservation_expiry');

            $table->dropColumn([
                'parent_contract_id',
                'created_by_sales_id',
                'approved_by_admin_id',
                'approved_by_pm_id',
                'terminated_by_admin_id',
                'terminated_by_pm_id',
                'booking_deposit_amount',
                'reservation_expires_at',
                'refund_deposit_amount',
                'penalty_deposit_amount',
            ]);
        });
    }
};
