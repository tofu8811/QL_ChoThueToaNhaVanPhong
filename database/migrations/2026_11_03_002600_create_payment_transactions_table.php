<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Giao dịch thanh toán tiền thuê tòa nhà (Thanh toán VietQR / Ủy nhiệm chi ngân hàng).
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID giao dịch thanh toán.
            $table->string('transaction_code', 40)->unique(); // Mã giao dịch duy nhất (VD: TXN-202611-0012).
            $table->foreignUuid('building_id')->constrained('buildings')->restrictOnDelete(); // Tòa nhà nhận tiền.
            $table->foreignUuid('tenant_id')->constrained('tenants')->restrictOnDelete(); // Khách thuê chuyển tiền.
            $table->foreignUuid('debit_note_id')->constrained('debitnotes')->restrictOnDelete(); // Hóa đơn cước được gạch nợ.
            $table->string('payment_method', 30)->default('BANK_TRANSFER'); // Phương thức: BANK_TRANSFER | VIETQR | CASH | CREDIT_CARD.
            $table->decimal('amount_paid', 18, 2); // Số tiền khách thực nộp / chuyển khoản (VND).
            $table->string('bank_reference_number', 100)->nullable(); // Mã bút toán / Mã tham chiếu từ sao kê ngân hàng.
            $table->string('proof_file_private_path', 255)->nullable(); // Ảnh chụp ủy nhiệm chi / biên lai chuyển khoản thành công.
            $table->string('receipt_pdf_private_path', 255)->nullable(); // File PDF phiếu thu tiền chính thức sau khi đối soát.
            $table->dateTime('paid_at'); // Thời gian tiền nổi trên tài khoản ngân hàng.
            $table->string('verification_status', 30)->default('PENDING'); // Trạng thái đối soát: PENDING (Chờ duyệt) | APPROVED (Đã khớp lệnh) | REJECTED (Sai lệch/từ chối).
            $table->foreignUuid('verified_by_accountant_id')->nullable()->constrained('accountants')->nullOnDelete(); // Kế toán xác thực số tiền.
            $table->dateTime('verified_at')->nullable(); // Thời điểm kế toán duyệt gạch nợ thành công.
            $table->string('accountant_note', 500)->nullable(); // Ghi chú đối soát của kế toán viên.
            $table->timestamps();

            $table->index(['building_id', 'verification_status', 'paid_at'], 'idx_pt_isolation_building');
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `payment_transactions` ADD CONSTRAINT `chk_pt_method` CHECK (`payment_method` IN ('BANK_TRANSFER', 'VIETQR', 'CASH', 'CREDIT_CARD'))");
            DB::statement("ALTER TABLE `payment_transactions` ADD CONSTRAINT `chk_pt_amount` CHECK (`amount_paid` > 0)");
            DB::statement("ALTER TABLE `payment_transactions` ADD CONSTRAINT `chk_pt_vstatus` CHECK (`verification_status` IN ('PENDING', 'APPROVED', 'REJECTED'))");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE payment_transactions ADD CONSTRAINT chk_pt_method CHECK (payment_method IN ('BANK_TRANSFER', 'VIETQR', 'CASH', 'CREDIT_CARD'))");
            DB::statement("ALTER TABLE payment_transactions ADD CONSTRAINT chk_pt_amount CHECK (amount_paid > 0)");
            DB::statement("ALTER TABLE payment_transactions ADD CONSTRAINT chk_pt_vstatus CHECK (verification_status IN ('PENDING', 'APPROVED', 'REJECTED'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
