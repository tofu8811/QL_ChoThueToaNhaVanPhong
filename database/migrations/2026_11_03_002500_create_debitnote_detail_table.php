<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Chi tiết các khoản mục trong hóa đơn cước (Tiền thuê sàn, Tiền điện, Nước, Xe máy, Xe ô tô, Ngoài giờ).
     */
    public function up(): void
    {
        Schema::create('debitnote_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('debit_note_id')->constrained('debitnotes')->cascadeOnDelete(); // Thuộc về giấy báo cước nào.
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete(); // Mặt bằng phát sinh chi phí tương ứng.
            $table->foreignId('utility_fee_id')->nullable()->constrained('utility_fee')->nullOnDelete(); // Liên kết bản ghi chốt chỉ số kỹ thuật.
            $table->string('item_category', 30); // Phân loại mục: RENT | SERVICE_FEE | ELECTRICITY | WATER | MOTORBIKE_PARKING | CAR_PARKING | OVERTIME | OTHER.
            $table->string('description', 255); // Diễn giải tên khoản chi phí trên phiếu thu.
            $table->decimal('quantity', 12, 2)->default(1.00); // Số lượng tiêu thụ (m2 diện tích, số kWh điện, số m3 nước, số lượt xe).
            $table->string('unit_name', 30); // Đơn vị tính (m2, kWh, m3, chiếc/tháng, giờ).
            $table->decimal('unit_price', 15, 2); // Đơn giá theo quy định tòa nhà (VND/đơn vị).
            $table->decimal('line_amount', 18, 2); // Thành tiền của mục này trước thuế (quantity * unit_price).
            $table->timestamps();
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `debitnote_detail` ADD CONSTRAINT `chk_dnd_category` CHECK (`item_category` IN ('RENT', 'SERVICE_FEE', 'ELECTRICITY', 'WATER', 'MOTORBIKE_PARKING', 'CAR_PARKING', 'OVERTIME', 'OTHER'))");
            DB::statement("ALTER TABLE `debitnote_detail` ADD CONSTRAINT `chk_dnd_quantity` CHECK (`quantity` > 0)");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE debitnote_detail ADD CONSTRAINT chk_dnd_category CHECK (item_category IN ('RENT', 'SERVICE_FEE', 'ELECTRICITY', 'WATER', 'MOTORBIKE_PARKING', 'CAR_PARKING', 'OVERTIME', 'OTHER'))");
            DB::statement("ALTER TABLE debitnote_detail ADD CONSTRAINT chk_dnd_quantity CHECK (quantity > 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debitnote_detail');
    }
};
