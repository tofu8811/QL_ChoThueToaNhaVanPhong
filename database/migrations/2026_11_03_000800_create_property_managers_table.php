<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Ban Quản Lý & Kỹ thuật tòa nhà (Property Managers - PM).
     */
    public function up(): void
    {
        Schema::create('property_managers', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID nhân sự BQL.
            $table->string('full_name', 120); // Họ tên nhân sự quản lý/kỹ thuật.
            $table->string('email', 150); // Email đăng nhập hệ thống nội bộ.
            $table->string('phone', 20)->nullable(); // Số điện thoại liên hệ trực ca.
            $table->string('password', 255); // Mật khẩu đăng nhập.
            $table->string('employee_code', 30)->unique(); // Mã định danh nhân viên BQL (VD: NV-BQL-001).
            $table->string('department', 50)->default('TECHNICAL'); // Bộ phận chuyên trách: TECHNICAL | SECURITY | CLEANING | CUSTOMER_CARE.
            $table->string('status', 20)->default('ACTIVE'); // Trạng thái: ACTIVE | LOCKED | PENDING.
            $table->foreignUuid('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Admin khởi tạo.
            $table->rememberToken();
            $table->dateTime('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'deleted_at'], 'idx_pm_email_lookup');
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
                DB::statement("CREATE UNIQUE INDEX `uq_pm_active_email` ON `property_managers` (`email`)");
                DB::statement("CREATE UNIQUE INDEX `uq_pm_active_phone` ON `property_managers` (`phone`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_pm_active_email` ON `property_managers` ((IF(`deleted_at` IS NULL, `email`, NULL)))");
                DB::statement("CREATE UNIQUE INDEX `uq_pm_active_phone` ON `property_managers` ((IF(`deleted_at` IS NULL, `phone`, NULL)))");
            }
            DB::statement("ALTER TABLE `property_managers` ADD CONSTRAINT `chk_pm_status` CHECK (`status` IN ('ACTIVE', 'LOCKED', 'PENDING'))");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_pm_active_email ON property_managers (email) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX uq_pm_active_phone ON property_managers (phone) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE property_managers ADD CONSTRAINT chk_pm_status CHECK (status IN ('ACTIVE', 'LOCKED', 'PENDING'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_managers');
    }
};
