<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Giấy báo cước / Hóa đơn tiền thuê & dịch vụ định kỳ (Debit Notes).
     */
    public function up(): void
    {
        Schema::create('debitnotes', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID hóa đơn cước.
            $table->string('debit_note_code', 40); // Mã hóa đơn cước (VD: DN-202611-0001).
            $table->foreignUuid('building_id')->constrained('buildings')->restrictOnDelete(); // Tòa nhà phát hành cước.
            $table->foreignUuid('tenant_id')->constrained('tenants')->restrictOnDelete(); // Khách thuê nhận thông báo cước.
            $table->foreignUuid('contract_id')->constrained('contracts')->restrictOnDelete(); // Hợp đồng căn cứ tính cước.
            $table->unsignedSmallInteger('billing_year'); // Năm phát hành thông báo cước.
            $table->unsignedTinyInteger('billing_month'); // Kỳ tính cước theo tháng (1 - 12).
            $table->string('note_type', 20)->default('REGULAR'); // Loại thông báo cước: REGULAR (Định kỳ) | ADJUSTMENT (Điều chỉnh) | FIRST_CYCLE (Kỳ đầu đặt cọc).
            $table->foreignUuid('reference_debit_note_id')->nullable()->constrained('debitnotes')->nullOnDelete(); // Liên kết hóa đơn gốc nếu là hóa đơn điều chỉnh.
            $table->date('issue_date')->nullable(); // Ngày kế toán ký duyệt và phát hành chính thức.
            $table->date('due_date'); // Ngày hạn chót khách thuê phải chuyển khoản thanh toán.
            $table->decimal('sub_total_amount', 18, 2)->default(0.00); // Tổng tiền cước trước thuế VAT (VND).
            $table->decimal('vat_percent', 5, 2)->default(10.00); // Tỷ lệ thuế suất giá trị gia tăng (% VAT).
            $table->decimal('previous_debt_amount', 18, 2)->default(0.00); // Tiền nợ đọng luân chuyển từ các kỳ trước chuyển sang (VND).
            $table->decimal('late_fee_amount', 18, 2)->default(0.00); // Tiền phạt chậm thanh toán phát sinh kỳ này (VND).
            $table->decimal('total_amount', 18, 2); // Tổng số tiền khách thuê phải thanh toán cuối cùng (VND).
            $table->decimal('paid_amount', 18, 2)->default(0.00); // Số tiền khách đã thanh toán thực tế vào tài khoản (VND).
            $table->string('pdf_private_path', 255)->nullable(); // File PDF hóa đơn cước có mã vạch/chữ ký (Private Bucket).
            $table->string('vietqr_payload', 500)->nullable(); // Chuỗi mã QR động tạo sẵn để khách quét VietQR thanh toán đúng số tiền.
            $table->boolean('is_immutable')->default(false); // Tính bất biến: True = Hóa đơn đã chốt phát hành, CẤM SỬA/XÓA.
            $table->string('status', 30)->default('DRAFT'); // Trạng thái hóa đơn: DRAFT | ISSUED | PARTIALLY_PAID | PAID | OVERDUE | DISPUTED | CANCELLED (Cast to DebitNoteStatus::class).
            $table->string('dispute_reason', 500)->nullable(); // Ghi chú khiếu nại của khách nếu có tranh chấp số đo chỉ số.
            $table->foreignUuid('created_by_accountant_id')->constrained('accountants')->restrictOnDelete(); // Kế toán lập phiếu tính nháp.
            $table->foreignUuid('issued_by_accountant_id')->nullable()->constrained('accountants')->nullOnDelete(); // Kế toán trưởng duyệt phát hành.
            $table->dateTime('issued_at')->nullable(); // Thời điểm phát hành hóa đơn chính thức.
            $table->timestamps();
            $table->softDeletes();

            $table->index('status', 'idx_dn_status');
            $table->index(['status', 'due_date'], 'idx_dn_overdue_scan');
            $table->index(['debit_note_code', 'deleted_at'], 'idx_dn_code_lookup');

            // Composite Foreign Key cô lập hóa đơn với hợp đồng theo tòa nhà và khách thuê
            $table->foreign(['building_id', 'tenant_id', 'contract_id'], 'fk_dn_contract_isolation')
                  ->references(['building_id', 'tenant_id', 'id'])->on('contracts')
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
                DB::statement("CREATE UNIQUE INDEX `uq_dn_active_code` ON `debitnotes` (`debit_note_code`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_dn_active_code` ON `debitnotes` ((IF(`deleted_at` IS NULL, `debit_note_code`, NULL)))");
            }
            DB::statement("ALTER TABLE `debitnotes` ADD CONSTRAINT `chk_dn_status` CHECK (`status` IN ('DRAFT', 'ISSUED', 'PARTIALLY_PAID', 'PAID', 'OVERDUE', 'DISPUTED', 'CANCELLED'))");
            DB::statement("ALTER TABLE `debitnotes` ADD CONSTRAINT `chk_dn_year` CHECK (`billing_year` >= 2024)");
            DB::statement("ALTER TABLE `debitnotes` ADD CONSTRAINT `chk_dn_month` CHECK (`billing_month` BETWEEN 1 AND 12)");
            DB::statement("ALTER TABLE `debitnotes` ADD CONSTRAINT `chk_dn_note_type` CHECK (`note_type` IN ('REGULAR', 'ADJUSTMENT', 'FIRST_CYCLE'))");
            DB::statement("ALTER TABLE `debitnotes` ADD CONSTRAINT `chk_dn_vat` CHECK (`vat_percent` >= 0)");
            DB::statement("ALTER TABLE `debitnotes` ADD CONSTRAINT `chk_dn_paid` CHECK (`paid_amount` >= 0)");
            DB::statement("ALTER TABLE `debitnotes` ADD CONSTRAINT `chk_dn_soft_delete_draft_only` CHECK (`is_immutable` = FALSE OR `deleted_at` IS NULL)");

            // Trigger bảo vệ tính Bất biến của Hóa đơn cước khi đã phát hành
            DB::unprepared("
                DROP TRIGGER IF EXISTS `trg_debitnotes_enforce_immutability`;
                CREATE TRIGGER `trg_debitnotes_enforce_immutability`
                BEFORE UPDATE ON `debitnotes`
                FOR EACH ROW
                BEGIN
                    IF OLD.is_immutable = TRUE THEN
                        IF (NEW.sub_total_amount != OLD.sub_total_amount) OR
                           (NEW.total_amount != OLD.total_amount) OR
                           (NEW.vat_percent != OLD.vat_percent) OR
                           (NEW.building_id != OLD.building_id) OR
                           (NEW.tenant_id != OLD.tenant_id) OR
                           (NEW.contract_id != OLD.contract_id) THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'LỖI BẢO MẬT: Hóa đơn đã phát hành chính thức (is_immutable = TRUE), cấm sửa đổi số tiền hoặc pháp lý. Vui lòng lập phiếu điều chỉnh (ADJUSTMENT).';
                        END IF;

                        IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'LỖI BẢO MẬT: Cấm xóa mềm hóa đơn đã phát hành chính thức. Chỉ được phép hủy hoặc điều chỉnh nghiệp vụ kế toán.';
                        END IF;
                    END IF;
                END;
            ");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_dn_active_code ON debitnotes (debit_note_code) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE debitnotes ADD CONSTRAINT chk_dn_status CHECK (status IN ('DRAFT', 'ISSUED', 'PARTIALLY_PAID', 'PAID', 'OVERDUE', 'DISPUTED', 'CANCELLED'))");
                DB::statement("ALTER TABLE debitnotes ADD CONSTRAINT chk_dn_year CHECK (billing_year >= 2024)");
                DB::statement("ALTER TABLE debitnotes ADD CONSTRAINT chk_dn_month CHECK (billing_month BETWEEN 1 AND 12)");
                DB::statement("ALTER TABLE debitnotes ADD CONSTRAINT chk_dn_note_type CHECK (note_type IN ('REGULAR', 'ADJUSTMENT', 'FIRST_CYCLE'))");
                DB::statement("ALTER TABLE debitnotes ADD CONSTRAINT chk_dn_vat CHECK (vat_percent >= 0)");
                DB::statement("ALTER TABLE debitnotes ADD CONSTRAINT chk_dn_paid CHECK (paid_amount >= 0)");
                DB::statement("ALTER TABLE debitnotes ADD CONSTRAINT chk_dn_soft_delete_draft_only CHECK (is_immutable = FALSE OR deleted_at IS NULL)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("DROP TRIGGER IF EXISTS `trg_debitnotes_enforce_immutability`;");
        }
        Schema::dropIfExists('debitnotes');
    }
};
