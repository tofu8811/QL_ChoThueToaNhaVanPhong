<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = Hash::make('Password@123');
        $bldId = '0192e220-0010-7000-8000-000000000001'; // The Nexus Tower

        // 1. Super Admin Demo
        $adminId = '0192e220-9999-7000-8000-000000000001';
        DB::table('admins')->updateOrInsert(
            ['email' => 'admin.demo@officeleasing.vn'],
            [
                'id' => $adminId,
                'full_name' => 'Trần Hoàng Quản Trị (Super Admin Demo)',
                'email' => 'admin.demo@officeleasing.vn',
                'phone' => '0909000001',
                'password' => $passwordHash,
                'is_super_admin' => true,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('users')->updateOrInsert(
            ['email' => 'admin.demo@officeleasing.vn'],
            [
                'name' => 'Trần Hoàng Quản Trị (Super Admin Demo)',
                'email' => 'admin.demo@officeleasing.vn',
                'password' => $passwordHash,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Ban Quản Lý (PM Demo)
        $pmId = '0192e220-9999-7000-8000-000000000002';
        DB::table('property_managers')->updateOrInsert(
            ['email' => 'pm.demo@officeleasing.vn'],
            [
                'id' => $pmId,
                'full_name' => 'Nguyễn Vận Hành (PM Demo)',
                'email' => 'pm.demo@officeleasing.vn',
                'phone' => '0909000002',
                'password' => $passwordHash,
                'employee_code' => 'NV-BQL-DEMO',
                'department' => 'TECHNICAL',
                'status' => 'ACTIVE',
                'created_by_admin_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('manager_buildings')->updateOrInsert(
            ['pm_id' => $pmId, 'building_id' => $bldId],
            [
                'is_primary' => true,
                'assigned_by_admin_id' => $adminId,
                'assigned_at' => now(),
            ]
        );

        // 3. Chuyên Viên Sales Demo
        $salesId = '0192e220-9999-7000-8000-000000000003';
        DB::table('sales')->updateOrInsert(
            ['email' => 'sales.demo@officeleasing.vn'],
            [
                'id' => $salesId,
                'full_name' => 'Phạm Kinh Doanh (Sales Demo)',
                'email' => 'sales.demo@officeleasing.vn',
                'phone' => '0909000003',
                'password' => $passwordHash,
                'employee_code' => 'NV-SALE-DEMO',
                'commission_rate' => 1.80,
                'max_concurrent_leads' => 25,
                'status' => 'ACTIVE',
                'created_by_admin_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('sale_buildings')->updateOrInsert(
            ['sales_id' => $salesId, 'building_id' => $bldId],
            [
                'is_active' => true,
                'assigned_by_admin_id' => $adminId,
                'assigned_at' => now(),
            ]
        );

        // 4. Kế Toán Demo
        $accId = '0192e220-9999-7000-8000-000000000004';
        DB::table('accountants')->updateOrInsert(
            ['email' => 'acc.demo@officeleasing.vn'],
            [
                'id' => $accId,
                'full_name' => 'Lê Kế Toán (Accountant Demo)',
                'email' => 'acc.demo@officeleasing.vn',
                'phone' => '0909000004',
                'password' => $passwordHash,
                'employee_code' => 'NV-KT-DEMO',
                'status' => 'ACTIVE',
                'created_by_admin_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        DB::table('account_buildings')->updateOrInsert(
            ['accountant_id' => $accId, 'building_id' => $bldId],
            [
                'assigned_by_admin_id' => $adminId,
                'assigned_at' => now(),
            ]
        );

        // 5. Khách Thuê Doanh Nghiệp (Tenant Demo)
        $tenantId = '0192e220-9999-7000-8000-000000000005';
        DB::table('tenants')->updateOrInsert(
            ['login_email' => 'tenant.demo@officeleasing.vn'],
            [
                'id' => $tenantId,
                'tenant_code' => 'TN-DEMO-2026',
                'company_name' => 'CÔNG TY CỔ PHẦN CÔNG NGHỆ DEMO TECH',
                'tax_code' => '0318889999',
                'representative_name' => 'Trần Công Nghệ',
                'representative_position' => 'Tổng Giám đốc',
                'headquarter_address' => 'Tầng 10 The Nexus Tower, Quận 1, TP.HCM',
                'login_email' => 'tenant.demo@officeleasing.vn',
                'billing_email' => 'accounting@demotech.vn',
                'contact_phone' => '02899998888',
                'password' => $passwordHash,
                'business_license_private_path' => 'tenants/TN-DEMO-2026/gpdkkd.pdf',
                'status' => 'ACTIVE',
                'created_by_sales_id' => $salesId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Gán 1 hợp đồng đang thuê cho Tenant Demo để kiểm tra workspace thực tế
        $contractId = '0192e220-9999-7000-8000-000000000006';
        DB::table('contracts')->updateOrInsert(
            ['contract_number' => 'HD-2026/DEMO-NEXUS'],
            [
                'id' => $contractId,
                'contract_number' => 'HD-2026/DEMO-NEXUS',
                'tenant_id' => $tenantId,
                'building_id' => $bldId,
                'status' => 'ACTIVE',
                'start_date' => '2026-01-01',
                'end_date' => '2028-12-31',
                'total_leased_area_sqm' => 250.00,
                'deposit_amount' => 525000000,
                'payment_cycle_months' => 3,
                'annual_price_escalation_percent' => 5.0,
                'created_by_sales_id' => $salesId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Gán 1 phiếu báo cước cho Tenant Demo
        $debitNoteId = '0192e220-9999-7000-8000-000000000007';
        DB::table('debitnotes')->updateOrInsert(
            ['debit_note_code' => 'DN-202611-DEMO'],
            [
                'id' => $debitNoteId,
                'debit_note_code' => 'DN-202611-DEMO',
                'tenant_id' => $tenantId,
                'building_id' => $bldId,
                'contract_id' => $contractId,
                'billing_year' => 2026,
                'billing_month' => 11,
                'note_type' => 'REGULAR',
                'issue_date' => '2026-11-01',
                'due_date' => '2026-11-15',
                'sub_total_amount' => 210000000,
                'vat_percent' => 10,
                'total_amount' => 231000000,
                'paid_amount' => 0,
                'status' => 'ISSUED',
                'created_by_accountant_id' => $accId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Gán 1 phiếu sự cố bảo trì do Tenant Demo tạo
        $ticketId = '0192e220-9999-7000-8000-000000000008';
        DB::table('tickets')->updateOrInsert(
            ['ticket_code' => 'TK-202611-DEMO'],
            [
                'id' => $ticketId,
                'ticket_code' => 'TK-202611-DEMO',
                'tenant_id' => $tenantId,
                'building_id' => $bldId,
                'category' => 'CONSTRUCTION',
                'title' => 'Đăng ký thi công lắp đặt bổ sung hệ thống mạng LAN nội bộ',
                'description' => 'Đăng ký cho 3 kỹ sư mạng thi công ngoài giờ vào tối thứ 6 tuần này từ 18:00 đến 21:00.',
                'priority' => 'NORMAL',
                'status' => 'IN_PROGRESS',
                'assigned_pm_id' => $pmId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
