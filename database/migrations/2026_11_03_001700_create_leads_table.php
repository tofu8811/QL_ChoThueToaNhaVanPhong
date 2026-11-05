<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng Khách hàng tiềm năng & Yêu cầu thuê sàn (Leads CRM).
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Khóa chính UUID của khách tiềm năng.
            $table->string('contact_full_name', 120); // Họ và tên người liên hệ đại diện doanh nghiệp.
            $table->string('company_name', 200)->nullable(); // Tên công ty có nhu cầu thuê mặt bằng.
            $table->string('phone', 20); // Số điện thoại liên hệ khách hàng.
            $table->string('email', 150); // Email trao đổi thông tin báo giá.
            $table->string('lead_source', 30)->default('WEBSITE'); // Nguồn khách hàng: WEBSITE | HOTLINE | DIRECT_WALK_IN | BROKER | ADS.
            $table->foreignUuid('building_id')->nullable()->constrained('buildings')->nullOnDelete(); // Tòa nhà khách quan tâm.
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete(); // Mặt bằng cụ thể khách muốn thuê.
            $table->decimal('desired_area_sqm', 10, 2)->nullable(); // Diện tích mong muốn thuê (m2).
            $table->decimal('budget_min', 15, 2)->nullable(); // Ngân sách tối thiểu dự kiến (VND).
            $table->decimal('budget_max', 15, 2)->nullable(); // Ngân sách tối đa dự kiến (VND).
            $table->dateTime('preferred_site_tour_at')->nullable(); // Thời gian khách mong muốn xem mặt bằng trực tiếp.
            $table->text('customer_note')->nullable(); // Ghi chú yêu cầu đặc biệt của khách hàng.
            $table->foreignUuid('assigned_sales_id')->nullable()->constrained('sales')->nullOnDelete(); // Chuyên viên kinh doanh được phân công chăm sóc.
            $table->dateTime('assigned_at')->nullable(); // Thời điểm gán khách cho Sales.
            $table->dateTime('sla_deadline_at')->nullable(); // Hạn chót Sales phải liên hệ lần đầu (SLA phục vụ).
            $table->dateTime('first_contacted_at')->nullable(); // Thời điểm Sales thực sự gọi điện/gặp mặt lần đầu.
            $table->string('status', 30)->default('NEW'); // Trạng thái phễu CRM: PENDING_ASSIGN | NEW | NEGOTIATING | WON | LOST (Cast to LeadStatus::class).
            $table->string('lost_reason_category', 100)->nullable(); // Lý do khách từ chối thuê (Giá cao, Không hợp hướng, Vị trí xa,...).
            $table->string('lost_reason_detail', 500)->nullable(); // Ghi chú phản hồi chi tiết khi thất bại.
            $table->uuid('converted_tenant_id')->nullable(); // ID khách thuê sau khi ký hợp đồng thành công (Tenants).
            $table->dateTime('converted_at')->nullable(); // Thời điểm chốt ký hợp đồng thành công.
            $table->timestamps();
            $table->softDeletes();

            $table->index('status', 'idx_leads_status');
            $table->index(['status', 'sla_deadline_at', 'first_contacted_at'], 'idx_leads_sla_monitor');
            $table->index(['building_id', 'assigned_sales_id', 'status'], 'idx_leads_isolation_sales');

            // Composite Foreign Key ràng buộc mặt bằng phải thuộc đúng tòa nhà
            $table->foreign(['building_id', 'unit_id'], 'fk_leads_building_unit')
                  ->references(['building_id', 'id'])->on('units')
                  ->nullOnDelete();
        });

        // Ràng buộc toàn vẹn & CHECK Constraints
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `leads` ADD CONSTRAINT `chk_leads_status` CHECK (`status` IN ('PENDING_ASSIGN', 'NEW', 'NEGOTIATING', 'WON', 'LOST'))");
            DB::statement("ALTER TABLE `leads` ADD CONSTRAINT `chk_leads_budget` CHECK (`budget_max` IS NULL OR `budget_min` IS NULL OR `budget_max` >= `budget_min`)");
            DB::statement("ALTER TABLE `leads` ADD CONSTRAINT `chk_leads_source` CHECK (`lead_source` IN ('WEBSITE', 'HOTLINE', 'DIRECT_WALK_IN', 'BROKER', 'FACEBOOK_ADS', 'GOOGLE_ADS', 'REFERRAL'))");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE leads ADD CONSTRAINT chk_leads_status CHECK (status IN ('PENDING_ASSIGN', 'NEW', 'NEGOTIATING', 'WON', 'LOST'))");
            DB::statement("ALTER TABLE leads ADD CONSTRAINT chk_leads_budget CHECK (budget_max IS NULL OR budget_min IS NULL OR budget_max >= budget_min)");
            DB::statement("ALTER TABLE leads ADD CONSTRAINT chk_leads_source CHECK (lead_source IN ('WEBSITE', 'HOTLINE', 'DIRECT_WALK_IN', 'BROKER', 'FACEBOOK_ADS', 'GOOGLE_ADS', 'REFERRAL'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
