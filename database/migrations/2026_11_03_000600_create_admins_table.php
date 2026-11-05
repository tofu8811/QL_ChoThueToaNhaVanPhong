<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng quản trị viên hệ thống (Ban điều hành toàn quyền).
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID định danh tài khoản quản trị.
            $table->string('full_name', 120); // Họ và tên đầy đủ của quản trị viên.
            $table->string('email', 150); // Email đăng nhập hệ thống quản trị.
            $table->string('phone', 20)->nullable(); // Số điện thoại liên hệ cá nhân.
            $table->string('password', 255); // Mật khẩu băm (Bcrypt / Argon2id).
            $table->boolean('is_super_admin')->default(false); // True: Quản trị viên tối cao; False: Quản trị viên thường.
            $table->string('status', 20)->default('ACTIVE'); // Trạng thái tài khoản: ACTIVE | LOCKED | PENDING.
            $table->rememberToken(); // Token ghi nhớ đăng nhập trình duyệt.
            $table->dateTime('last_login_at')->nullable(); // Thời điểm đăng nhập thành công gần nhất.
            $table->timestamps(); // Thời gian tạo và cập nhật bản ghi.
            $table->softDeletes(); // Hỗ trợ xóa mềm phục vụ Audit Log.

            $table->index(['email', 'deleted_at'], 'idx_admins_email_lookup');
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
                DB::statement("CREATE UNIQUE INDEX `uq_admins_active_email` ON `admins` (`email`)");
                DB::statement("CREATE UNIQUE INDEX `uq_admins_active_phone` ON `admins` (`phone`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_admins_active_email` ON `admins` ((IF(`deleted_at` IS NULL, `email`, NULL)))");
                DB::statement("CREATE UNIQUE INDEX `uq_admins_active_phone` ON `admins` ((IF(`deleted_at` IS NULL, `phone`, NULL)))");
            }
            DB::statement("ALTER TABLE `admins` ADD CONSTRAINT `chk_admins_status` CHECK (`status` IN ('ACTIVE', 'LOCKED', 'PENDING'))");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_admins_active_email ON admins (email) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX uq_admins_active_phone ON admins (phone) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE admins ADD CONSTRAINT chk_admins_status CHECK (status IN ('ACTIVE', 'LOCKED', 'PENDING'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
