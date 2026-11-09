<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Nạp dữ liệu toàn diện cho 24 bảng CSDL:
     * 1. StaffAndAccountSeeder: Nhân sự đa vai trò (Admins, Sales, PMs, Accountants)
     * 2. BuildingAndUnitSeeder: 22 Tòa nhà khảo sát từ niceoffice.com.vn & 110 Mặt bằng
     * 3. OperationalBusinessSeeder: Phân công, Khách thuê, Hợp đồng, Chỉ số, Hóa đơn, Giao dịch, Leads CRM, Tickets
     */
    public function run(): void
    {
        $this->call([
            StaffAndAccountSeeder::class,
            ProvinceSeeder::class,
            BuildingAndUnitSeeder::class,
            OperationalBusinessSeeder::class,
        ]);
    }
}
