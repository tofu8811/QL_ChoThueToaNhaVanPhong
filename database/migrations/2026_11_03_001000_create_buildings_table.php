<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Tòa nhà văn phòng (Buildings) - Thực thể lõi trung tâm.
     */
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID tòa nhà.
            $table->string('building_code', 20); // Mã định danh tòa nhà (VD: TN-BITEXCO, TDA-01).
            $table->string('name', 180); // Tên thương mại của tòa nhà văn phòng.
            $table->string('slug', 200); // URL thân thiện SEO cho cổng tra cứu public.
            $table->string('address', 255); // Địa chỉ số nhà và tên đường.
            $table->string('ward', 100); // Phường / Xã.
            $table->string('district', 100); // Quận / Huyện.
            $table->string('province', 100); // Tỉnh / Thành phố trực thuộc trung ương.
            $table->decimal('latitude', 10, 8)->nullable(); // Vĩ độ GPS phục vụ Google Maps & bản đồ số.
            $table->decimal('longitude', 11, 8)->nullable(); // Kinh độ GPS phục vụ Google Maps & bản đồ số.
            $table->unsignedSmallInteger('total_floors'); // Tổng số tầng nổi của tòa nhà.
            $table->unsignedTinyInteger('total_basements')->default(0); // Số lượng tầng hầm để xe.
            $table->decimal('total_leasable_area_sqm', 12, 2); // Tổng diện tích sàn khả dụng có thể cho thuê (m2).
            $table->decimal('ceiling_height_meters', 4, 2)->default(2.70); // Chiều cao trần tiêu chuẩn (mét).
            $table->unsignedTinyInteger('elevators_count')->default(1); // Số lượng thang máy chở khách và hàng.
            $table->string('air_conditioner_spec', 150)->nullable(); // Thông số hệ thống điều hòa (Trung tâm Chiller / VRV / Cục bộ).
            $table->string('backup_generator_spec', 150)->nullable(); // Công suất máy phát điện dự phòng (VD: 100% công suất 24/7).
            $table->string('working_hours_desc', 150)->nullable(); // Giờ làm việc tiêu chuẩn (VD: Thứ 2-6: 8h-18h, Thứ 7: 8h-12h).
            $table->decimal('overtime_fee_per_hour', 15, 2)->default(0.00); // Phí sử dụng điều hòa ngoài giờ (VND/giờ).
            $table->decimal('motorbike_parking_fee', 15, 2)->default(0.00); // Phí gửi xe máy tháng (VND/xe/tháng).
            $table->decimal('car_parking_fee', 15, 2)->default(0.00); // Phí gửi xe ô tô tháng (VND/xe/tháng).
            $table->decimal('electricity_unit_price', 15, 2)->default(0.00); // Đơn giá điện (VND/kWh theo công tơ hoặc giá nhà nước).
            $table->decimal('water_unit_price', 15, 2)->default(0.00); // Đơn giá nước sạch (VND/m3).
            $table->decimal('default_service_fee_per_sqm', 15, 2)->default(0.00); // Phí quản lý dịch vụ mặc định (VND/m2/tháng).
            $table->decimal('min_rent_price_per_sqm', 15, 2)->default(0.00); // Khoảng giá thuê thấp nhất chào thị trường (VND/m2).
            $table->decimal('max_rent_price_per_sqm', 15, 2)->default(0.00); // Khoảng giá thuê cao nhất chào thị trường (VND/m2).
            $table->unsignedTinyInteger('deposit_term_months')->default(3); // Quy định số tháng tiền cọc tối thiểu (VD: 3 tháng).
            $table->unsignedTinyInteger('payment_term_months')->default(3); // Kỳ hạn thanh toán chuẩn (VD: 3 tháng/lần).
            $table->string('grade', 10)->default('B'); // Phân hạng tòa nhà văn phòng: Hạng A, B, C.
            $table->string('direction', 30)->nullable(); // Hướng chính diện tòa nhà theo phong thủy (Đông, Tây, Nam, Bắc,...).
            $table->boolean('is_published')->default(true); // Trạng thái hiển thị công khai trên Portal tìm kiếm.
            $table->foreignUuid('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete(); // Admin khởi tạo thông tin tòa nhà.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'province', 'district', 'grade'], 'idx_buildings_public_search');
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
                DB::statement("CREATE UNIQUE INDEX `uq_buildings_active_code` ON `buildings` (`building_code`)");
                DB::statement("CREATE UNIQUE INDEX `uq_buildings_active_slug` ON `buildings` (`slug`)");
            } else {
                DB::statement("CREATE UNIQUE INDEX `uq_buildings_active_code` ON `buildings` ((IF(`deleted_at` IS NULL, `building_code`, NULL)))");
                DB::statement("CREATE UNIQUE INDEX `uq_buildings_active_slug` ON `buildings` ((IF(`deleted_at` IS NULL, `slug`, NULL)))");
            }
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_grade` CHECK (`grade` IN ('A', 'B', 'C'))");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_total_floors` CHECK (`total_floors` > 0)");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_leasable_area` CHECK (`total_leasable_area_sqm` > 0)");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_service_fee` CHECK (`default_service_fee_per_sqm` >= 0)");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_elec_price` CHECK (`electricity_unit_price` >= 0)");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_water_price` CHECK (`water_unit_price` >= 0)");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_mb_fee` CHECK (`motorbike_parking_fee` >= 0)");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_car_fee` CHECK (`car_parking_fee` >= 0)");
            DB::statement("ALTER TABLE `buildings` ADD CONSTRAINT `chk_buildings_ot_fee` CHECK (`overtime_fee_per_hour` >= 0)");
        } elseif (in_array($driver, ['pgsql', 'sqlite'])) {
            DB::statement("CREATE UNIQUE INDEX uq_buildings_active_code ON buildings (building_code) WHERE deleted_at IS NULL");
            DB::statement("CREATE UNIQUE INDEX uq_buildings_active_slug ON buildings (slug) WHERE deleted_at IS NULL");
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_grade CHECK (grade IN ('A', 'B', 'C'))");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_total_floors CHECK (total_floors > 0)");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_leasable_area CHECK (total_leasable_area_sqm > 0)");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_service_fee CHECK (default_service_fee_per_sqm >= 0)");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_elec_price CHECK (electricity_unit_price >= 0)");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_water_price CHECK (water_unit_price >= 0)");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_mb_fee CHECK (motorbike_parking_fee >= 0)");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_car_fee CHECK (car_parking_fee >= 0)");
                DB::statement("ALTER TABLE buildings ADD CONSTRAINT chk_buildings_ot_fee CHECK (overtime_fee_per_hour >= 0)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
