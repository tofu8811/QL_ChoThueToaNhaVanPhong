<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Hợp đồng thuê văn phòng (Contracts) - Quản lý pháp lý & chu kỳ thanh toán.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID hợp đồng.
            $table->string('contract_number', 50); // Số hiệu hợp đồng pháp lý (VD: HĐ-2026/01-TDA).
            $table->foreignUuid('building_id')->constrained('buildings')->restrictOnDelete(); // Tòa nhà cho thuê.
            $table->foreignUuid('tenant_id')->constrained('tenants')->restrictOnDelete(); // Doanh nghiệp đứng tên thuê.
            $table->date('signed_date'); // Ngày hai bên chính thức ký kết hợp đồng.
            $table->date('start_date'); // Ngày bắt đầu tính tiền thuê và bàn giao mặt bằng.
            $table->date('end_date'); // Ngày kết thúc hiệu lực hợp đồng thuê.
            $table->decimal('total_leased_area_sqm', 12, 2); // Tổng diện tích thực tế thuê (m2).
            $table->decimal('deposit_amount', 18, 2); // Tiền đặt cọc giữ chỗ và bảo đảm hợp đồng (VND).
            $table->unsignedTinyInteger('payment_cycle_months')->default(1); // Chu kỳ thanh toán: 1 (hàng tháng), 3 (quý), 6 (nửa năm), 12 (cả năm).
            $table->unsignedTinyInteger('payment_due_day')->default(5); // Ngày đến hạn nộp tiền thuê định kỳ (Từ ngày 1 đến ngày 28 hàng tháng).
            $table->decimal('late_fee_interest_percent', 5, 2)->default(0.05); // Tỷ lệ lãi suất phạt chậm nộp (%/ngày).
            $table->decimal('annual_price_escalation_percent', 5, 2)->default(5.00); // Tỷ lệ tăng giá thuê theo năm (%/năm theo biên độ thị trường).
            $table->string('contract_pdf_private_path', 255); // File PDF scan hợp đồng có chữ ký/con dấu (Private Bucket).
            $table->string('handover_minutes_private_path', 255)->nullable(); // Biên bản bàn giao hiện trạng mặt bằng.
            $table->string('status', 30)->default('DRAFT'); // Trạng thái hợp đồng: DRAFT | ACTIVE | EXPIRING_SOON | EXPIRED | TERMINATED (Cast to ContractStatus::class).
            $table->dateTime('expiring_notified_at')->nullable(); // Thời điểm hệ thống đã tự động gửi email báo sắp hết hạn hợp đồng.
            $table->date('terminated_at')->nullable(); // Ngày chấm dứt hợp đồng sớm trước hạn (nếu có thanh lý).
            $table->string('termination_note', 500)->nullable(); // Lý do thanh lý hoặc đền bù vi phạm hợp đồng.
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'id'], 'uq_contracts_building_id_pair'); // Composite unique phục vụ liên kết composite FK.
            $table->unique(['building_id', 'tenant_id', 'id'], 'uq_contracts_isolation_tuple'); // Đảm bảo cô lập dữ liệu 3 chiều.
            $table->index('status', 'idx_contracts_status');
            $table->index(['status', 'end_date'], 'idx_contracts_expiry_scan_30d');
            $table->index(['building_id', 'status', 'end_date'], 'idx_contracts_isolation_building');
            $table->index(['tenant_id', 'status'], 'idx_contracts_isolation_tenant');
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
                DB::statement("CREATE UNIQUE INDEX `uq_contracts_active_number` ON `contracts` (`contract_number`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_contracts_active_number` ON `contracts` ((IF(`deleted_at` IS NULL, `contract_number`, NULL)))");
            }
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_status` CHECK (`status` IN ('DRAFT', 'ACTIVE', 'EXPIRING_SOON', 'EXPIRED', 'TERMINATED'))");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_dates` CHECK (`end_date` >= `start_date`)");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_area` CHECK (`total_leased_area_sqm` > 0)");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_deposit` CHECK (`deposit_amount` >= 0)");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_cycle` CHECK (`payment_cycle_months` IN (1, 3, 6, 12))");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_due_day` CHECK (`payment_due_day` BETWEEN 1 AND 28)");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_late_fee` CHECK (`late_fee_interest_percent` >= 0)");
            DB::statement("ALTER TABLE `contracts` ADD CONSTRAINT `chk_contracts_escalation` CHECK (`annual_price_escalation_percent` >= 0)");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_contracts_active_number ON contracts (contract_number) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_status CHECK (status IN ('DRAFT', 'ACTIVE', 'EXPIRING_SOON', 'EXPIRED', 'TERMINATED'))");
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_dates CHECK (end_date >= start_date)");
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_area CHECK (total_leased_area_sqm > 0)");
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_deposit CHECK (deposit_amount >= 0)");
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_cycle CHECK (payment_cycle_months IN (1, 3, 6, 12))");
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_due_day CHECK (payment_due_day BETWEEN 1 AND 28)");
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_late_fee CHECK (late_fee_interest_percent >= 0)");
                DB::statement("ALTER TABLE contracts ADD CONSTRAINT chk_contracts_escalation CHECK (annual_price_escalation_percent >= 0)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
