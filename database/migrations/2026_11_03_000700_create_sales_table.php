<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Chuyên viên kinh doanh / Môi giới cho thuê (Sales Agents).
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID nhân viên kinh doanh.
            $table->string('full_name', 120); // Họ và tên nhân viên Sales.
            $table->string('email', 150); // Email doanh nghiệp dùng đăng nhập.
            $table->string('phone', 20)->nullable(); // Số điện thoại liên hệ hotline/zalo.
            $table->string('password', 255); // Mật khẩu đăng nhập.
            $table->string('employee_code', 30)->unique(); // Mã định danh nhân viên (VD: NV-SALE-001).
            $table->decimal('commission_rate', 5, 2)->default(0.00); // Tỷ lệ hoa hồng môi giới chốt cọc (% trên giá trị hợp đồng).
            $table->unsignedSmallInteger('max_concurrent_leads')->default(20); // Giới hạn số lead tối đa đang xử lý đồng thời.
            $table->string('status', 20)->default('ACTIVE'); // Trạng thái: ACTIVE | LOCKED | PENDING.
            $table->foreignUuid('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Admin khởi tạo tài khoản.
            $table->rememberToken();
            $table->dateTime('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'deleted_at'], 'idx_sales_email_lookup');
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
                DB::statement("CREATE UNIQUE INDEX `uq_sales_active_email` ON `sales` (`email`)");
                DB::statement("CREATE UNIQUE INDEX `uq_sales_active_phone` ON `sales` (`phone`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_sales_active_email` ON `sales` ((IF(`deleted_at` IS NULL, `email`, NULL)))");
                DB::statement("CREATE UNIQUE INDEX `uq_sales_active_phone` ON `sales` ((IF(`deleted_at` IS NULL, `phone`, NULL)))");
            }
            DB::statement("ALTER TABLE `sales` ADD CONSTRAINT `chk_sales_status` CHECK (`status` IN ('ACTIVE', 'LOCKED', 'PENDING'))");
            DB::statement("ALTER TABLE `sales` ADD CONSTRAINT `chk_sales_max_concurrent` CHECK (`max_concurrent_leads` > 0)");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_sales_active_email ON sales (email) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX uq_sales_active_phone ON sales (phone) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE sales ADD CONSTRAINT chk_sales_status CHECK (status IN ('ACTIVE', 'LOCKED', 'PENDING'))");
                DB::statement("ALTER TABLE sales ADD CONSTRAINT chk_sales_max_concurrent CHECK (max_concurrent_leads > 0)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
