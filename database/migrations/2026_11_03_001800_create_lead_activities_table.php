<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nhật ký hoạt động tương tác với khách hàng tiềm năng (Lead Activity History).
     */
    public function up(): void
    {
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('lead_id')->constrained('leads')->cascadeOnDelete(); // Khách tiềm năng được tương tác.
            $table->foreignUuid('sales_id')->nullable()->constrained('sales')->nullOnDelete(); // Chuyên viên kinh doanh thực hiện.
            $table->string('activity_type', 30); // Loại hoạt động: CALL | SITE_TOUR | EMAIL | MEETING | NOTE | QUOTATION_SENT.
            $table->string('title', 255); // Tiêu đề ngắn tóm tắt hoạt động.
            $table->text('content')->nullable(); // Nội dung chi tiết kết quả buổi làm việc với khách.
            $table->dateTime('event_at')->nullable(); // Thời gian diễn ra sự kiện/cuộc gọi.
            $table->string('attachment_path', 255)->nullable(); // Tài liệu đính kèm (Biên bản khảo sát, file chào giá,...).
            $table->timestamp('created_at')->useCurrent();
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `lead_activities` ADD CONSTRAINT `chk_la_type` CHECK (`activity_type` IN ('CALL', 'SITE_TOUR', 'EMAIL', 'MEETING', 'NOTE', 'QUOTATION_SENT'))");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE lead_activities ADD CONSTRAINT chk_la_type CHECK (activity_type IN ('CALL', 'SITE_TOUR', 'EMAIL', 'MEETING', 'NOTE', 'QUOTATION_SENT'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
