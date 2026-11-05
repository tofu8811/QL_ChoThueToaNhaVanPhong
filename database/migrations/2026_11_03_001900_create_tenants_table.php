<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Doanh nghiệp thuê văn phòng chính thức (Tenants Portal & Entity).
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID khách thuê.
            $table->string('tenant_code', 30); // Mã định danh khách thuê (VD: TN-2026-001).
            $table->foreignUuid('converted_from_lead_id')->nullable()->constrained('leads')->nullOnDelete(); // Nguồn gốc lead chuyển đổi thành khách chính thức.
            $table->string('company_name', 200); // Tên đầy đủ của doanh nghiệp thuê mặt bằng.
            $table->string('tax_code', 30); // Mã số thuế doanh nghiệp (Bắt buộc cho khách hàng B2B).
            $table->string('business_license_private_path', 255); // File PDF Giấy phép kinh doanh (Lưu trong Private Bucket).
            $table->string('representative_name', 120); // Họ tên người đại diện pháp luật của doanh nghiệp.
            $table->string('representative_position', 100); // Chức vụ của người đại diện (Giám đốc, Tổng giám đốc,...).
            $table->string('headquarter_address', 255); // Địa chỉ trụ sở chính theo ĐKKD.
            $table->string('login_email', 150); // Email đăng nhập Cổng thông tin khách thuê (Tenant Portal).
            $table->string('billing_email', 150); // Email chính nhận thông báo cước và hóa đơn VAT điện tử.
            $table->string('contact_phone', 20); // Số điện thoại liên hệ khẩn cấp với khách thuê.
            $table->string('password', 255); // Mật khẩu băm cổng Portal khách thuê.
            $table->string('status', 20)->default('ACTIVE'); // Trạng thái: ACTIVE | LOCKED | PENDING.
            $table->rememberToken();
            $table->dateTime('last_login_at')->nullable();
            $table->foreignUuid('created_by_sales_id')->nullable()->constrained('sales')->nullOnDelete(); // Sales chốt hợp đồng.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['login_email', 'deleted_at'], 'idx_tenants_email_lookup');
            $table->index(['tax_code', 'deleted_at'], 'idx_tenants_tax_lookup');
        });

        // Liên kết ngược FK từ leads.converted_tenant_id tới tenants.id
        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('converted_tenant_id', 'fk_leads_converted_tenant')
                  ->references('id')->on('tenants')
                  ->nullOnDelete();
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
                DB::statement("CREATE UNIQUE INDEX `uq_tenants_active_code` ON `tenants` (`tenant_code`)");
                DB::statement("CREATE UNIQUE INDEX `uq_tenants_active_tax` ON `tenants` (`tax_code`)");
                DB::statement("CREATE UNIQUE INDEX `uq_tenants_active_login_email` ON `tenants` (`login_email`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_tenants_active_code` ON `tenants` ((IF(`deleted_at` IS NULL, `tenant_code`, NULL)))");
                DB::statement("CREATE UNIQUE INDEX `uq_tenants_active_tax` ON `tenants` ((IF(`deleted_at` IS NULL, `tax_code`, NULL)))");
                DB::statement("CREATE UNIQUE INDEX `uq_tenants_active_login_email` ON `tenants` ((IF(`deleted_at` IS NULL, `login_email`, NULL)))");
            }
            DB::statement("ALTER TABLE `tenants` ADD CONSTRAINT `chk_tenants_status` CHECK (`status` IN ('ACTIVE', 'LOCKED', 'PENDING'))");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_tenants_active_code ON tenants (tenant_code) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX uq_tenants_active_tax ON tenants (tax_code) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX uq_tenants_active_login_email ON tenants (login_email) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE tenants ADD CONSTRAINT chk_tenants_status CHECK (status IN ('ACTIVE', 'LOCKED', 'PENDING'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign('fk_leads_converted_tenant');
        });

        Schema::dropIfExists('tenants');
    }
};
