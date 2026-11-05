<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Chi tiết các mặt bằng được thuê trong hợp đồng (Một hợp đồng có thể thuê nhiều sàn).
     */
    public function up(): void
    {
        Schema::create('contract_units', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('building_id')->constrained('buildings')->cascadeOnDelete(); // Tòa nhà trực thuộc.
            $table->foreignUuid('contract_id')->constrained('contracts')->cascadeOnDelete(); // Hợp đồng thuê mặt bằng.
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete(); // Mặt bằng cụ thể được giao.
            $table->decimal('leased_area_sqm', 10, 2); // Diện tích thực tế chốt thuê trong hợp đồng (m2).
            $table->decimal('agreed_rental_price_per_sqm', 15, 2); // Đơn giá thuê đã thỏa thuận theo hợp đồng (VND/m2/tháng).
            $table->decimal('agreed_service_fee_per_sqm', 15, 2)->default(0.00); // Phí dịch vụ quản lý tòa nhà chốt theo hợp đồng (VND/m2/tháng).
            $table->decimal('monthly_base_rent', 18, 2)->storedAs('leased_area_sqm * agreed_rental_price_per_sqm'); // Tiền thuê cơ sở tự động tính.
            $table->decimal('monthly_service_fee', 18, 2)->storedAs('leased_area_sqm * agreed_service_fee_per_sqm'); // Tiền dịch vụ tự động tính.
            $table->timestamps();

            $table->unique(['contract_id', 'unit_id'], 'uq_contract_units_pair'); // Một mặt bằng không được thêm 2 lần trong cùng 1 hợp đồng.

            // Composite Foreign Keys chống lệch tòa nhà
            $table->foreign(['building_id', 'contract_id'], 'fk_cu_contract_building')
                  ->references(['building_id', 'id'])->on('contracts')
                  ->cascadeOnDelete();

            $table->foreign(['building_id', 'unit_id'], 'fk_cu_unit_building')
                  ->references(['building_id', 'id'])->on('units')
                  ->restrictOnDelete();
        });

        // Ràng buộc toàn vẹn & CHECK Constraints
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `contract_units` ADD CONSTRAINT `chk_cu_area` CHECK (`leased_area_sqm` > 0)");
            DB::statement("ALTER TABLE `contract_units` ADD CONSTRAINT `chk_cu_rent_price` CHECK (`agreed_rental_price_per_sqm` > 0)");
            DB::statement("ALTER TABLE `contract_units` ADD CONSTRAINT `chk_cu_service_fee` CHECK (`agreed_service_fee_per_sqm` >= 0)");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE contract_units ADD CONSTRAINT chk_cu_area CHECK (leased_area_sqm > 0)");
            DB::statement("ALTER TABLE contract_units ADD CONSTRAINT chk_cu_rent_price CHECK (agreed_rental_price_per_sqm > 0)");
            DB::statement("ALTER TABLE contract_units ADD CONSTRAINT chk_cu_service_fee CHECK (agreed_service_fee_per_sqm >= 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_units');
    }
};
