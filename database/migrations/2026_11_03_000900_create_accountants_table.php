<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Kế toán công nợ tòa nhà (Accountants).
     */
    public function up(): void
    {
        Schema::create('accountants', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID kế toán viên.
            $table->string('full_name', 120); // Họ tên kế toán viên.
            $table->string('email', 150); // Email đăng nhập phân hệ tài chính.
            $table->string('phone', 20)->nullable(); // Số điện thoại liên hệ kế toán.
            $table->string('password', 255); // Mật khẩu đăng nhập.
            $table->string('employee_code', 30)->unique(); // Mã định danh nhân viên (VD: NV-KT-001).
            $table->string('status', 20)->default('ACTIVE'); // Trạng thái: ACTIVE | LOCKED | PENDING.
            $table->foreignUuid('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Admin khởi tạo.
            $table->rememberToken();
            $table->dateTime('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'deleted_at'], 'idx_acc_email_lookup');
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
                DB::statement("CREATE UNIQUE INDEX `uq_acc_active_email` ON `accountants` (`email`)");
                DB::statement("CREATE UNIQUE INDEX `uq_acc_active_phone` ON `accountants` (`phone`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_acc_active_email` ON `accountants` ((IF(`deleted_at` IS NULL, `email`, NULL)))");
                DB::statement("CREATE UNIQUE INDEX `uq_acc_active_phone` ON `accountants` ((IF(`deleted_at` IS NULL, `phone`, NULL)))");
            }
            DB::statement("ALTER TABLE `accountants` ADD CONSTRAINT `chk_accountants_status` CHECK (`status` IN ('ACTIVE', 'LOCKED', 'PENDING'))");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_acc_active_email ON accountants (email) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX uq_acc_active_phone ON accountants (phone) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE accountants ADD CONSTRAINT chk_accountants_status CHECK (status IN ('ACTIVE', 'LOCKED', 'PENDING'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accountants');
    }
};
