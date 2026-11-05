<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Ghi nhận chỉ số tiêu thụ điện, nước, xe, làm thêm ngoài giờ hàng tháng.
     */
    public function up(): void
    {
        Schema::create('utility_fee', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('building_id')->constrained('buildings')->restrictOnDelete(); // Tòa nhà phát sinh chỉ số.
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete(); // Mặt bằng tiêu thụ.
            $table->unsignedSmallInteger('billing_year'); // Năm chốt chỉ số tính cước (VD: 2026).
            $table->unsignedTinyInteger('billing_month'); // Tháng chốt chỉ số (1 - 12).
            $table->date('reading_date'); // Ngày nhân viên kỹ thuật đi chốt đồng hồ thực tế.
            $table->decimal('elec_start_index', 12, 2); // Chỉ số điện đầu kỳ (kWh).
            $table->decimal('elec_end_index', 12, 2); // Chỉ số điện cuối kỳ (kWh).
            $table->decimal('water_start_index', 12, 2); // Chỉ số nước đầu kỳ (m3).
            $table->decimal('water_end_index', 12, 2); // Chỉ số nước cuối kỳ (m3).
            $table->unsignedSmallInteger('motorbike_count')->default(0); // Số lượng xe máy đăng ký đỗ thường xuyên trong tháng.
            $table->unsignedSmallInteger('car_count')->default(0); // Số lượng ô tô đăng ký đỗ thường xuyên trong tháng.
            $table->decimal('overtime_hours', 8, 2)->default(0.00); // Tổng số giờ làm việc ngoài giờ có điều hòa trong kỳ.
            $table->string('meter_photo_private_path', 255)->nullable(); // Ảnh chụp công tơ đồng hồ làm minh chứng đối soát.
            $table->foreignUuid('recorded_by_pm_id')->constrained('property_managers')->restrictOnDelete(); // Nhân viên kỹ thuật ghi nhận số liệu.
            $table->boolean('is_locked')->default(false); // True: Số liệu đã chốt đưa vào hóa đơn (không được sửa); False: Đang mở chỉnh sửa.
            $table->timestamps();

            $table->unique(['unit_id', 'billing_year', 'billing_month'], 'uq_utility_unit_period');

            // Composite Foreign Key ràng buộc phòng thuộc đúng tòa nhà
            $table->foreign(['building_id', 'unit_id'], 'fk_uf_unit_building')
                  ->references(['building_id', 'id'])->on('units')
                  ->restrictOnDelete();
        });

        // Ràng buộc toàn vẹn & CHECK Constraints
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `utility_fee` ADD CONSTRAINT `chk_uf_year` CHECK (`billing_year` >= 2024)");
            DB::statement("ALTER TABLE `utility_fee` ADD CONSTRAINT `chk_uf_month` CHECK (`billing_month` BETWEEN 1 AND 12)");
            DB::statement("ALTER TABLE `utility_fee` ADD CONSTRAINT `chk_uf_elec_start` CHECK (`elec_start_index` >= 0)");
            DB::statement("ALTER TABLE `utility_fee` ADD CONSTRAINT `chk_uf_elec_end` CHECK (`elec_end_index` >= `elec_start_index`)");
            DB::statement("ALTER TABLE `utility_fee` ADD CONSTRAINT `chk_uf_water_start` CHECK (`water_start_index` >= 0)");
            DB::statement("ALTER TABLE `utility_fee` ADD CONSTRAINT `chk_uf_water_end` CHECK (`water_end_index` >= `water_start_index`)");
            DB::statement("ALTER TABLE `utility_fee` ADD CONSTRAINT `chk_uf_overtime` CHECK (`overtime_hours` >= 0)");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE utility_fee ADD CONSTRAINT chk_uf_year CHECK (billing_year >= 2024)");
            DB::statement("ALTER TABLE utility_fee ADD CONSTRAINT chk_uf_month CHECK (billing_month BETWEEN 1 AND 12)");
            DB::statement("ALTER TABLE utility_fee ADD CONSTRAINT chk_uf_elec_start CHECK (elec_start_index >= 0)");
            DB::statement("ALTER TABLE utility_fee ADD CONSTRAINT chk_uf_elec_end CHECK (elec_end_index >= elec_start_index)");
            DB::statement("ALTER TABLE utility_fee ADD CONSTRAINT chk_uf_water_start CHECK (water_start_index >= 0)");
            DB::statement("ALTER TABLE utility_fee ADD CONSTRAINT chk_uf_water_end CHECK (water_end_index >= water_start_index)");
            DB::statement("ALTER TABLE utility_fee ADD CONSTRAINT chk_uf_overtime CHECK (overtime_hours >= 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_fee');
    }
};
