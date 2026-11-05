<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffAndAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Khởi tạo nhân sự đa vai trò phục vụ hệ thống phân quyền (Multi-Guard RBAC):
     * - 02 Quản trị viên hệ thống (Admins)
     * - 03 Chuyên viên kinh doanh / Môi giới (Sales)
     * - 03 Ban Quản Lý & Kỹ thuật tòa nhà (Property Managers)
     * - 02 Kế toán công nợ tòa nhà (Accountants)
     */
    public function run(): void
    {
        $superAdminId = '0192e220-0001-7000-8000-000000000001';
        $opsAdminId   = '0192e220-0001-7000-8000-000000000002';

        // 0. Tài khoản mặc định Laravel Auth (Bảng users) phục vụ đăng nhập thông thường & API Sanctum
        $users = [
            [
                'name' => 'Nguyễn Quản Trị Hệ Thống (Super Admin)',
                'email' => 'admin@officeleasing.vn',
                'password' => Hash::make('Admin@123456'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trần Vận Hành Doanh Nghiệp (Ops Admin)',
                'email' => 'op.admin@officeleasing.vn',
                'password' => Hash::make('Admin@123456'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(['email' => $user['email']], $user);
        }

        // 1. Quản trị viên (Admins) - Phục vụ Multi-Guard RBAC chuyên sâu
        $admins = [
            [
                'id' => $superAdminId,
                'full_name' => 'Nguyễn Quản Trị Hệ Thống',
                'email' => 'admin@officeleasing.vn',
                'phone' => '0901234567',
                'password' => Hash::make('Admin@123456'),
                'is_super_admin' => true,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $opsAdminId,
                'full_name' => 'Trần Vận Hành Doanh Nghiệp',
                'email' => 'op.admin@officeleasing.vn',
                'phone' => '0901234568',
                'password' => Hash::make('Admin@123456'),
                'is_super_admin' => false,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($admins as $admin) {
            DB::table('admins')->updateOrInsert(['email' => $admin['email']], $admin);
        }

        // 2. Chuyên viên kinh doanh (Sales Agents)
        $sales = [
            [
                'id' => '0192e220-0002-7000-8000-000000000001',
                'full_name' => 'Lê Hoàng Long',
                'email' => 'sales.hoang@officeleasing.vn',
                'phone' => '0912345601',
                'password' => Hash::make('Sales@123456'),
                'employee_code' => 'NV-SALE-001',
                'commission_rate' => 1.50,
                'max_concurrent_leads' => 25,
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '0192e220-0002-7000-8000-000000000002',
                'full_name' => 'Phạm Phương Linh',
                'email' => 'sales.linh@officeleasing.vn',
                'phone' => '0912345602',
                'password' => Hash::make('Sales@123456'),
                'employee_code' => 'NV-SALE-002',
                'commission_rate' => 1.50,
                'max_concurrent_leads' => 20,
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '0192e220-0002-7000-8000-000000000003',
                'full_name' => 'Vũ Anh Tuấn',
                'email' => 'sales.tuan@officeleasing.vn',
                'phone' => '0912345603',
                'password' => Hash::make('Sales@123456'),
                'employee_code' => 'NV-SALE-003',
                'commission_rate' => 2.00,
                'max_concurrent_leads' => 30,
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sales as $sale) {
            DB::table('sales')->updateOrInsert(['email' => $sale['email']], $sale);
        }

        // 3. Ban Quản Lý Tòa Nhà & Kỹ Thuật (Property Managers - PM)
        $managers = [
            [
                'id' => '0192e220-0003-7000-8000-000000000001',
                'full_name' => 'Đỗ Minh Quang',
                'email' => 'pm.quang@officeleasing.vn',
                'phone' => '0922334401',
                'password' => Hash::make('Pm@123456'),
                'employee_code' => 'NV-BQL-001',
                'department' => 'TECHNICAL',
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '0192e220-0003-7000-8000-000000000002',
                'full_name' => 'Ngô Văn Minh',
                'email' => 'pm.minh@officeleasing.vn',
                'phone' => '0922334402',
                'password' => Hash::make('Pm@123456'),
                'employee_code' => 'NV-BQL-002',
                'department' => 'TECHNICAL',
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '0192e220-0003-7000-8000-000000000003',
                'full_name' => 'Bùi Tiến Dũng',
                'email' => 'pm.dung@officeleasing.vn',
                'phone' => '0922334403',
                'password' => Hash::make('Pm@123456'),
                'employee_code' => 'NV-BQL-003',
                'department' => 'CUSTOMER_CARE',
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($managers as $pm) {
            DB::table('property_managers')->updateOrInsert(['email' => $pm['email']], $pm);
        }

        // 4. Kế toán công nợ tòa nhà (Accountants)
        $accountants = [
            [
                'id' => '0192e220-0004-7000-8000-000000000001',
                'full_name' => 'Hoàng Minh Thư',
                'email' => 'acc.thu@officeleasing.vn',
                'phone' => '0933445501',
                'password' => Hash::make('Accountant@123456'),
                'employee_code' => 'NV-KT-001',
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '0192e220-0004-7000-8000-000000000002',
                'full_name' => 'Võ Thị Thu Hằng',
                'email' => 'acc.hang@officeleasing.vn',
                'phone' => '0933445502',
                'password' => Hash::make('Accountant@123456'),
                'employee_code' => 'NV-KT-002',
                'status' => 'ACTIVE',
                'created_by_admin_id' => $superAdminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($accountants as $acc) {
            DB::table('accountants')->updateOrInsert(['email' => $acc['email']], $acc);
        }
    }
}
