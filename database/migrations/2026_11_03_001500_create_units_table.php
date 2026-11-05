<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Mặt bằng / Sàn văn phòng cho thuê (Units).
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID mặt bằng.
            $table->foreignUuid('building_id')->constrained('buildings')->restrictOnDelete(); // Tòa nhà chứa mặt bằng này.
            $table->string('unit_code', 30); // Mã căn / phòng (VD: P.801, FL12-A, UNIT-502).
            $table->string('name', 150); // Tên hiển thị đầy đủ mặt bằng văn phòng.
            $table->smallInteger('floor_number'); // Tầng chứa mặt bằng (Số âm là tầng hầm, số dương là tầng nổi).
            $table->decimal('area_sqm', 10, 2); // Diện tích thông thủy cho thuê (m2).
            $table->decimal('base_rental_price', 15, 2); // Đơn giá thuê cơ sở niêm yết (VND/m2/tháng).
            $table->string('handover_condition', 30)->default('BASIC'); // Hiện trạng bàn giao: RAW (Thô) | BASIC (Cơ bản) | FULL_FURNISHED (Đủ nội thất).
            $table->string('direction', 50)->nullable(); // Hướng cửa / ban công phong thủy (Đông, Tây, Nam, Bắc,...).
            $table->string('floor_plan_image_url', 255)->nullable(); // Bản vẽ sơ đồ mặt bằng chi tiết (Layout CAD / 2D).
            $table->date('available_from_date')->nullable(); // Ngày sẵn sàng bàn giao cho khách thuê vào làm việc.
            $table->string('status', 30)->default('AVAILABLE'); // Trạng thái mặt bằng: AVAILABLE | EXPIRING_SOON | LEASED | MAINTENANCE (Cast to UnitStatus::class).
            $table->dateTime('last_status_changed_at')->useCurrent(); // Thời điểm thay đổi trạng thái gần nhất.
            $table->foreignUuid('updated_by_pm_id')->nullable()->constrained('property_managers')->nullOnDelete(); // Ban quản lý cập nhật hiện trạng.
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'id'], 'uq_units_building_id_pair'); // Composite key làm đích đến cho Composite FKs chống lệch tòa nhà.
            $table->index('status', 'idx_units_status');
            $table->index(['status', 'building_id', 'area_sqm', 'base_rental_price'], 'idx_units_public_filter');
            $table->index(['status', 'base_rental_price', 'area_sqm'], 'idx_units_price_area');
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
                DB::statement("CREATE UNIQUE INDEX `uq_units_active_building_code` ON `units` (`building_id`, `unit_code`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_units_active_building_code` ON `units` (`building_id`, (IF(`deleted_at` IS NULL, `unit_code`, NULL)))");
            }
            DB::statement("ALTER TABLE `units` ADD CONSTRAINT `chk_units_status` CHECK (`status` IN ('AVAILABLE', 'EXPIRING_SOON', 'LEASED', 'MAINTENANCE'))");
            DB::statement("ALTER TABLE `units` ADD CONSTRAINT `chk_units_area` CHECK (`area_sqm` > 0)");
            DB::statement("ALTER TABLE `units` ADD CONSTRAINT `chk_units_price` CHECK (`base_rental_price` > 0)");
            DB::statement("ALTER TABLE `units` ADD CONSTRAINT `chk_units_handover` CHECK (`handover_condition` IN ('RAW', 'BASIC', 'FULL_FURNISHED'))");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_units_active_building_code ON units (building_id, unit_code) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE units ADD CONSTRAINT chk_units_status CHECK (status IN ('AVAILABLE', 'EXPIRING_SOON', 'LEASED', 'MAINTENANCE'))");
                DB::statement("ALTER TABLE units ADD CONSTRAINT chk_units_area CHECK (area_sqm > 0)");
                DB::statement("ALTER TABLE units ADD CONSTRAINT chk_units_price CHECK (base_rental_price > 0)");
                DB::statement("ALTER TABLE units ADD CONSTRAINT chk_units_handover CHECK (handover_condition IN ('RAW', 'BASIC', 'FULL_FURNISHED'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
