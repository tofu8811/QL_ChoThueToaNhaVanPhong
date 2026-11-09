<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OperationalBusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Khởi tạo toàn bộ dữ liệu quy trình nghiệp vụ lõi (Business Logic Layer):
     * 1. Phân công tòa nhà (sale_buildings, manager_buildings, account_buildings)
     * 2. Doanh nghiệp khách thuê (tenants)
     * 3. Hợp đồng thuê & phân bổ mặt bằng (contracts, contract_units)
     * 4. Ghi nhận chỉ số điện nước & dịch vụ (utility_fee)
     * 5. Hóa đơn cước phí & chi tiết hóa đơn (debitnotes, debitnote_detail)
     * 6. Giao dịch thanh toán VietQR (payment_transactions)
     * 7. Khách hàng tiềm năng & Lịch sử chăm sóc (leads, lead_activities)
     * 8. Phiếu hỗ trợ kỹ thuật tòa nhà & Lịch sử xử lý (tickets, ticket_history)
     */
    public function run(): void
    {
        $superAdminId = '0192e220-0001-7000-8000-000000000001';

        // Lấy danh sách ID nhân viên đã tạo từ StaffAndAccountSeeder
        $salesList = DB::table('sales')->pluck('id', 'employee_code')->toArray();
        $pmList    = DB::table('property_managers')->pluck('id', 'employee_code')->toArray();
        $accList   = DB::table('accountants')->pluck('id', 'employee_code')->toArray();
        $buildings = DB::table('buildings')->pluck('id', 'building_code')->toArray();

        // 1. Phân công phụ trách tòa nhà
        $this->seedBuildingAssignments($superAdminId, $buildings, $salesList, $pmList, $accList);

        // 2. Khởi tạo Khách thuê Doanh nghiệp (Tenants)
        $tenants = $this->seedTenants($salesList);

        // 3. Khởi tạo Hợp đồng & Mặt bằng thuê (Contracts & Contract Units)
        $contracts = $this->seedContracts($buildings, $tenants);

        // 4. Khởi tạo Chỉ số tiêu thụ điện nước dịch vụ (Utility Fee)
        $utilityFees = $this->seedUtilityFees($buildings, $pmList);

        // 5. Khởi tạo Hóa đơn cước phí & Chi tiết hóa đơn (Debit Notes & Details)
        $debitNotes = $this->seedDebitNotesAndDetails($contracts, $utilityFees, $accList);

        // 6. Khởi tạo Giao dịch thanh toán VietQR / Chuyển khoản (Payment Transactions)
        $this->seedPaymentTransactions($debitNotes, $accList);

        // 7. Khởi tạo Khách hàng tiềm năng CRM & Hoạt động chăm sóc (Leads & Activities)
        $this->seedLeadsAndActivities($buildings, $salesList, $tenants);

        // 8. Khởi tạo Vé hỗ trợ sự cố kỹ thuật & Lịch sử xử lý (Tickets & History)
        $this->seedTicketsAndHistory($contracts, $pmList);
    }

    private function seedBuildingAssignments(string $adminId, array $buildings, array $sales, array $pms, array $accs): void
    {
        $saleIds = array_values($sales);
        $pmIds   = array_values($pms);
        $accIds  = array_values($accs);

        $idx = 0;
        foreach ($buildings as $code => $bId) {
            // Phân công Sales
            $assignedSalesId = $saleIds[$idx % count($saleIds)];
            DB::table('sale_buildings')->updateOrInsert(
                ['sales_id' => $assignedSalesId, 'building_id' => $bId],
                ['is_active' => true, 'assigned_by_admin_id' => $adminId, 'assigned_at' => now()]
            );

            // Phân công Ban Quản Lý (PM)
            $assignedPmId = $pmIds[$idx % count($pmIds)];
            DB::table('manager_buildings')->updateOrInsert(
                ['pm_id' => $assignedPmId, 'building_id' => $bId],
                ['is_primary' => true, 'assigned_by_admin_id' => $adminId, 'assigned_at' => now()]
            );

            // Phân công Kế toán
            $assignedAccId = $accIds[$idx % count($accIds)];
            DB::table('account_buildings')->updateOrInsert(
                ['accountant_id' => $assignedAccId, 'building_id' => $bId],
                ['assigned_by_admin_id' => $adminId, 'assigned_at' => now()]
            );

            $idx++;
        }
    }

    private function seedTenants(array $salesList): array
    {
        $salesId1 = $salesList['NV-SALE-001'] ?? null;
        $salesId2 = $salesList['NV-SALE-002'] ?? null;
        $salesId3 = $salesList['NV-SALE-003'] ?? null;

        $tenantsData = [
            [
                'id' => '0192e220-0030-7000-8000-000000000001',
                'tenant_code' => 'TN-2026-001',
                'company_name' => 'CÔNG TY CỔ PHẦN VNG',
                'tax_code' => '0303588502',
                'representative_name' => 'Lê Hồng Minh',
                'representative_position' => 'Chủ tịch HĐQT',
                'headquarter_address' => 'Z06 Đường số 13, Phường Tân Thuận Đông, Quận 7, TP.HCM',
                'login_email' => 'contact@vng.com.vn',
                'billing_email' => 'accounting@vng.com.vn',
                'contact_phone' => '02839623888',
                'created_by_sales_id' => $salesId1,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000002',
                'tenant_code' => 'TN-2026-002',
                'company_name' => 'CÔNG TY TNHH SHOPEE (VIỆT NAM)',
                'tax_code' => '0313467659',
                'representative_name' => 'Trần Tuấn Anh',
                'representative_position' => 'Giám đốc Điều hành',
                'headquarter_address' => 'Tầng 17 Saigon Centre 2, 67 Lê Lợi, Bến Nghé, Quận 1, TP.HCM',
                'login_email' => 'admin@shopee.vn',
                'billing_email' => 'finance@shopee.vn',
                'contact_phone' => '02873081221',
                'created_by_sales_id' => $salesId1,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000003',
                'tenant_code' => 'TN-2026-003',
                'company_name' => 'CÔNG TY TNHH PHẦN MỀM FPT (FPT SOFTWARE)',
                'tax_code' => '0101601092',
                'representative_name' => 'Phạm Minh Tuấn',
                'representative_position' => 'Tổng Giám đốc',
                'headquarter_address' => 'Tòa nhà F-Town, Khu Công nghệ cao, TP. Thủ Đức, TP.HCM',
                'login_email' => 'contact@fpt-software.com',
                'billing_email' => 'fpt.accounting@fpt.com',
                'contact_phone' => '02873007575',
                'created_by_sales_id' => $salesId2,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000004',
                'tenant_code' => 'TN-2026-004',
                'company_name' => 'NGÂN HÀNG THƯƠNG MẠI CỔ PHẦN TIÊN PHONG (TPBANK)',
                'tax_code' => '0102744865',
                'representative_name' => 'Nguyễn Hưng',
                'representative_position' => 'Tổng Giám đốc',
                'headquarter_address' => 'Tòa nhà TPBank, 57 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội',
                'login_email' => 'leasing@tpb.com.vn',
                'billing_email' => 'finance@tpb.com.vn',
                'contact_phone' => '1900585885',
                'created_by_sales_id' => $salesId2,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000005',
                'tenant_code' => 'TN-2026-005',
                'company_name' => 'CÔNG TY TNHH LOGISTICS CAINIAO VIỆT NAM',
                'tax_code' => '0316447819',
                'representative_name' => 'Zhang Wei',
                'representative_position' => 'Giám đốc Quốc gia',
                'headquarter_address' => 'Tầng 9 Mapletree Business Centre, 1060 Nguyễn Văn Linh, Quận 7, TP.HCM',
                'login_email' => 'vietnam@cainiao.com',
                'billing_email' => 'ap-finance@cainiao.com',
                'contact_phone' => '02854136688',
                'created_by_sales_id' => $salesId3,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000006',
                'tenant_code' => 'TN-2026-006',
                'company_name' => 'CÔNG TY CỔ PHẦN DỊCH VỤ DI ĐỘNG TRỰC TUYẾN (VÍ MOMO)',
                'tax_code' => '0305345689',
                'representative_name' => 'Nguyễn Bá Diệp',
                'representative_position' => 'Đồng sáng lập & Phó Chủ tịch',
                'headquarter_address' => 'Tầng M, Tòa nhà PetroVietnam, Số 1-5 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM',
                'login_email' => 'contact@momo.vn',
                'billing_email' => 'ketoan@momo.vn',
                'contact_phone' => '02839209868',
                'created_by_sales_id' => $salesId1,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000007',
                'tenant_code' => 'TN-2026-007',
                'company_name' => 'CÔNG TY TNHH TƯ VẤN QUỐC TẾ MCKINSEY VIETNAM',
                'tax_code' => '0309988771',
                'representative_name' => 'Marco Breu',
                'representative_position' => 'Tổng Giám đốc Điều hành',
                'headquarter_address' => 'Tầng 11 Deutsches Haus, 33 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM',
                'login_email' => 'vietnam_office@mckinsey.com',
                'billing_email' => 'vn_billing@mckinsey.com',
                'contact_phone' => '02838271900',
                'created_by_sales_id' => $salesId3,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000008',
                'tenant_code' => 'TN-2026-008',
                'company_name' => 'CÔNG TY CỔ PHẦN TẬP ĐOÀN ĐẦU TƯ ĐỊA ỐC NOVA',
                'tax_code' => '0301444753',
                'representative_name' => 'Bùi Thành Nhơn',
                'representative_position' => 'Chủ tịch HĐQT',
                'headquarter_address' => 'Số 25Bis Nguyễn Thị Minh Khai, Bến Nghé, Quận 1, TP.HCM',
                'login_email' => 'info@novaland.com.vn',
                'billing_email' => 'accounting@novaland.com.vn',
                'contact_phone' => '02835210553',
                'created_by_sales_id' => $salesId2,
            ],
            [
                'id' => '0192e220-0030-7000-8000-000000000009',
                'tenant_code' => 'TN-2026-009',
                'company_name' => 'CÔNG TY TNHH GRAB (VIỆT NAM)',
                'tax_code' => '0312650437',
                'representative_name' => 'Alejandro Osorio',
                'representative_position' => 'Giám đốc Điều hành',
                'headquarter_address' => 'Tòa nhà MapleTree, 1060 Nguyễn Văn Linh, Quận 7, TP.HCM',
                'login_email' => 'partner.vn@grab.com',
                'billing_email' => 'finance.vn@grab.com',
                'contact_phone' => '02871087108',
                'created_by_sales_id' => $salesId1,
            ],
            [
                'id' => '0192e220-0030-7000-8000-0000000010',
                'tenant_code' => 'TN-2026-010',
                'company_name' => 'CÔNG TY CỔ PHẦN CHỨNG KHOÁN SSI',
                'tax_code' => '0301955150',
                'representative_name' => 'Nguyễn Duy Hưng',
                'representative_position' => 'Chủ tịch HĐQT',
                'headquarter_address' => 'Số 72 Nguyễn Huệ, Bến Nghé, Quận 1, TP.HCM',
                'login_email' => 'contact@ssi.com.vn',
                'billing_email' => 'ketoan@ssi.com.vn',
                'contact_phone' => '02838242898',
                'created_by_sales_id' => $salesId3,
            ],
        ];

        $result = [];
        foreach ($tenantsData as $t) {
            $record = array_merge($t, [
                'business_license_private_path' => 'tenants/' . $t['tenant_code'] . '/gpdkkd.pdf',
                'password' => Hash::make('Tenant@123456'),
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tenants')->updateOrInsert(['tenant_code' => $t['tenant_code']], $record);
            $result[$t['tenant_code']] = $t['id'];
        }

        return $result;
    }

    private function seedContracts(array $buildings, array $tenants): array
    {
        // Danh sách các hợp đồng hoạt động (khớp chính xác các Units trạng thái LEASED)
        $contractsConfig = [
            [
                'id' => '0192e220-0040-7000-8000-000000000001',
                'contract_number' => 'HD-2026/01-NEXUS',
                'building_code' => 'NEXUS-TOWER',
                'tenant_code' => 'TN-2026-001', // VNG
                'unit_code' => 'NEX-0801',
                'signed_date' => '2025-12-15',
                'start_date' => '2026-01-01',
                'end_date' => '2028-12-31',
                'leased_area' => 200.00,
                'rent_price' => 1500000.00, // 1.5 triệu/m2
                'service_fee' => 200000.00, // 200k/m2
                'deposit_amount' => 1020000000.00, // 3 tháng cọc: 3 x (300tr + 40tr) = 1.02 tỷ
                'payment_cycle' => 1,
                'due_day' => 5,
                'status' => 'ACTIVE',
            ],
            [
                'id' => '0192e220-0040-7000-8000-000000000002',
                'contract_number' => 'HD-2026/02-SGC2',
                'building_code' => 'SAIGON-CENTRE-2',
                'tenant_code' => 'TN-2026-002', // Shopee
                'unit_code' => 'SGC2-1502',
                'signed_date' => '2025-05-10',
                'start_date' => '2025-06-01',
                'end_date' => '2028-05-31',
                'leased_area' => 200.00,
                'rent_price' => 1500000.00,
                'service_fee' => 200000.00,
                'deposit_amount' => 1020000000.00,
                'payment_cycle' => 3,
                'due_day' => 5,
                'status' => 'ACTIVE',
            ],
            [
                'id' => '0192e220-0040-7000-8000-000000000003',
                'contract_number' => 'HD-2026/03-DH',
                'building_code' => 'DEUTSCHES-HAUS',
                'tenant_code' => 'TN-2026-007', // McKinsey
                'unit_code' => 'DH-1102',
                'signed_date' => '2024-02-15',
                'start_date' => '2024-03-01',
                'end_date' => '2027-02-28',
                'leased_area' => 300.00,
                'rent_price' => 1500000.00,
                'service_fee' => 200000.00,
                'deposit_amount' => 1530000000.00,
                'payment_cycle' => 3,
                'due_day' => 10,
                'status' => 'ACTIVE',
            ],
            [
                'id' => '0192e220-0040-7000-8000-000000000004',
                'contract_number' => 'HD-2026/04-STS',
                'building_code' => 'TIMES-SQUARE',
                'tenant_code' => 'TN-2026-006', // MoMo
                'unit_code' => 'STS-1401',
                'signed_date' => '2025-01-20',
                'start_date' => '2025-02-01',
                'end_date' => '2028-01-31',
                'leased_area' => 250.00,
                'rent_price' => 1500000.00,
                'service_fee' => 200000.00,
                'deposit_amount' => 1275000000.00,
                'payment_cycle' => 1,
                'due_day' => 5,
                'status' => 'ACTIVE',
            ],
            [
                'id' => '0192e220-0040-7000-8000-000000000005',
                'contract_number' => 'HD-2026/05-PLP',
                'building_code' => 'PEARL-PLAZA',
                'tenant_code' => 'TN-2026-003', // FPT Software
                'unit_code' => 'PLP-1201',
                'signed_date' => '2024-12-10',
                'start_date' => '2025-01-01',
                'end_date' => '2027-12-31',
                'leased_area' => 280.00,
                'rent_price' => 700000.00,
                'service_fee' => 140000.00,
                'deposit_amount' => 705600000.00,
                'payment_cycle' => 3,
                'due_day' => 5,
                'status' => 'ACTIVE',
            ],
            [
                'id' => '0192e220-0040-7000-8000-000000000006',
                'contract_number' => 'HD-2026/06-OPL',
                'building_code' => 'OPAL-TOWER',
                'tenant_code' => 'TN-2026-009', // Grab
                'unit_code' => 'OPL-1401',
                'signed_date' => '2025-03-25',
                'start_date' => '2025-04-01',
                'end_date' => '2027-03-31',
                'leased_area' => 350.00,
                'rent_price' => 620000.00,
                'service_fee' => 130000.00,
                'deposit_amount' => 787500000.00,
                'payment_cycle' => 1,
                'due_day' => 5,
                'status' => 'ACTIVE',
            ],
            [
                'id' => '0192e220-0040-7000-8000-000000000007',
                'contract_number' => 'HD-2026/07-MTB',
                'building_code' => 'MAPLETREE-BC',
                'tenant_code' => 'TN-2026-005', // Cainiao Logistics
                'unit_code' => 'MTB-0901',
                'signed_date' => '2024-10-15',
                'start_date' => '2024-11-01',
                'end_date' => '2026-11-30', // Sắp hết hạn trong tháng 11/2026
                'leased_area' => 380.00,
                'rent_price' => 800000.00,
                'service_fee' => 150000.00,
                'deposit_amount' => 1083000000.00,
                'payment_cycle' => 1,
                'due_day' => 5,
                'status' => 'EXPIRING_SOON',
            ],
            [
                'id' => '0192e220-0040-7000-8000-000000000008',
                'contract_number' => 'HD-2026/08-CP',
                'building_code' => 'CENTREPOINT-PN',
                'tenant_code' => 'TN-2026-004', // TPBank
                'unit_code' => 'CP-1001',
                'signed_date' => '2025-02-20',
                'start_date' => '2025-03-01',
                'end_date' => '2028-02-28',
                'leased_area' => 300.00,
                'rent_price' => 660000.00,
                'service_fee' => 140000.00,
                'deposit_amount' => 720000000.00,
                'payment_cycle' => 3,
                'due_day' => 5,
                'status' => 'ACTIVE',
            ],
        ];

        $result = [];

        foreach ($contractsConfig as $c) {
            $buildingId = $buildings[$c['building_code']];
            $tenantId   = $tenants[$c['tenant_code']];

            // Tìm unit id tương ứng
            $unit = DB::table('units')
                ->where('building_id', $buildingId)
                ->where('unit_code', $c['unit_code'])
                ->first();

            if (!$unit) continue;

            $contractRecord = [
                'id' => $c['id'],
                'contract_number' => $c['contract_number'],
                'building_id' => $buildingId,
                'tenant_id' => $tenantId,
                'signed_date' => $c['signed_date'],
                'start_date' => $c['start_date'],
                'end_date' => $c['end_date'],
                'total_leased_area_sqm' => $c['leased_area'],
                'deposit_amount' => $c['deposit_amount'],
                'payment_cycle_months' => $c['payment_cycle'],
                'payment_due_day' => $c['due_day'],
                'late_fee_interest_percent' => 0.05,
                'annual_price_escalation_percent' => 5.00,
                'contract_pdf_private_path' => 'contracts/' . $c['contract_number'] . '/signed.pdf',
                'handover_minutes_private_path' => 'contracts/' . $c['contract_number'] . '/handover.pdf',
                'status' => $c['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('contracts')->updateOrInsert(['contract_number' => $c['contract_number']], $contractRecord);

            // Gán mặt bằng vào hợp đồng (Lưu ý: monthly_base_rent và monthly_service_fee là generated columns)
            DB::table('contract_units')->updateOrInsert(
                [
                    'contract_id' => $c['id'],
                    'unit_id' => $unit->id,
                ],
                [
                    'building_id' => $buildingId,
                    'leased_area_sqm' => $c['leased_area'],
                    'agreed_rental_price_per_sqm' => $c['rent_price'],
                    'agreed_service_fee_per_sqm' => $c['service_fee'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $result[$c['contract_number']] = [
                'contract_id' => $c['id'],
                'building_id' => $buildingId,
                'tenant_id' => $tenantId,
                'unit_id' => $unit->id,
                'config' => $c,
            ];
        }

        return $result;
    }

    private function seedUtilityFees(array $buildings, array $pmList): array
    {
        $pmId1 = $pmList['NV-BQL-001'];
        $pmId2 = $pmList['NV-BQL-002'];

        $nexusId = $buildings['NEXUS-TOWER'];
        $sgc2Id  = $buildings['SAIGON-CENTRE-2'];

        $nexusUnit = DB::table('units')->where('building_id', $nexusId)->where('unit_code', 'NEX-0801')->first();
        $sgc2Unit  = DB::table('units')->where('building_id', $sgc2Id)->where('unit_code', 'SGC2-1502')->first();

        $utilityRecords = [];

        if ($nexusUnit) {
            // Tháng 10/2026 - Đã chốt hóa đơn
            $utilityRecords['NEX-10'] = [
                'building_id' => $nexusId,
                'unit_id' => $nexusUnit->id,
                'billing_year' => 2026,
                'billing_month' => 10,
                'reading_date' => '2026-10-28',
                'elec_start_index' => 12000.00,
                'elec_end_index' => 15500.00, // Tiêu thụ 3500 kWh
                'water_start_index' => 150.00,
                'water_end_index' => 210.00,  // Tiêu thụ 60 m3
                'motorbike_count' => 35,
                'car_count' => 4,
                'overtime_hours' => 15.00,
                'recorded_by_pm_id' => $pmId1,
                'is_locked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Tháng 11/2026 - Kỳ tính mới nhất
            $utilityRecords['NEX-11'] = [
                'building_id' => $nexusId,
                'unit_id' => $nexusUnit->id,
                'billing_year' => 2026,
                'billing_month' => 11,
                'reading_date' => '2026-11-04',
                'elec_start_index' => 15500.00,
                'elec_end_index' => 18800.00, // Tiêu thụ 3300 kWh
                'water_start_index' => 210.00,
                'water_end_index' => 265.00,  // Tiêu thụ 55 m3
                'motorbike_count' => 35,
                'car_count' => 4,
                'overtime_hours' => 10.00,
                'recorded_by_pm_id' => $pmId1,
                'is_locked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($sgc2Unit) {
            $utilityRecords['SGC-10'] = [
                'building_id' => $sgc2Id,
                'unit_id' => $sgc2Unit->id,
                'billing_year' => 2026,
                'billing_month' => 10,
                'reading_date' => '2026-10-28',
                'elec_start_index' => 8500.00,
                'elec_end_index' => 11200.00, // 2700 kWh
                'water_start_index' => 110.00,
                'water_end_index' => 155.00,  // 45 m3
                'motorbike_count' => 25,
                'car_count' => 3,
                'overtime_hours' => 8.00,
                'recorded_by_pm_id' => $pmId2,
                'is_locked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $inserted = [];
        foreach ($utilityRecords as $key => $rec) {
            DB::table('utility_fee')->updateOrInsert(
                [
                    'unit_id' => $rec['unit_id'],
                    'billing_year' => $rec['billing_year'],
                    'billing_month' => $rec['billing_month'],
                ],
                $rec
            );

            $row = DB::table('utility_fee')
                ->where('unit_id', $rec['unit_id'])
                ->where('billing_year', $rec['billing_year'])
                ->where('billing_month', $rec['billing_month'])
                ->first();

            $inserted[$key] = $row->id;
        }

        return $inserted;
    }

    private function seedDebitNotesAndDetails(array $contracts, array $utilityFees, array $accList): array
    {
        $accId1 = $accList['NV-KT-001'];
        $nexusContract = $contracts['HD-2026/01-NEXUS'] ?? null;

        if (!$nexusContract) return [];

        $c = $nexusContract;
        $buildingId = $c['building_id'];
        $tenantId   = $c['tenant_id'];
        $contractId = $c['contract_id'];
        $unitId     = $c['unit_id'];

        // 1. Hóa đơn Tháng 10/2026 - ĐÃ THANH TOÁN TOÀN BỘ (PAID)
        $dn10Id = '0192e220-0050-7000-8000-000000000001';
        $dn10Code = 'DN-202610-0001';

        // Tính toán các mục thành phần làm tròn chẵn số tiền
        $rentLine    = 300000000.00; // 200m2 x 1.5tr
        $serviceLine = 40000000.00;  // 200m2 x 200k
        $elecLine    = 15750000.00;  // 3500 kWh x 4500đ
        $waterLine   = 1500000.00;   // 60 m3 x 25000đ
        $motorLine   = 17500000.00;  // 35 xe x 500k
        $carLine     = 25200000.00;  // 4 xe x 6.3tr
        $otLine      = 5250000.00;   // 15 giờ x 350k

        $subTotal10 = $rentLine + $serviceLine + $elecLine + $waterLine + $motorLine + $carLine + $otLine; // 405.200.000đ
        $vat10      = $subTotal10 * 0.10; // 40.520.000đ
        $total10    = $subTotal10 + $vat10; // 445.720.000đ

        $dn10Record = [
            'id' => $dn10Id,
            'debit_note_code' => $dn10Code,
            'building_id' => $buildingId,
            'tenant_id' => $tenantId,
            'contract_id' => $contractId,
            'billing_year' => 2026,
            'billing_month' => 10,
            'note_type' => 'REGULAR',
            'issue_date' => '2026-10-25',
            'due_date' => '2026-11-05',
            'sub_total_amount' => $subTotal10,
            'vat_percent' => 10.00,
            'previous_debt_amount' => 0.00,
            'late_fee_amount' => 0.00,
            'total_amount' => $total10,
            'paid_amount' => $total10,
            'is_immutable' => true,
            'status' => 'PAID',
            'created_by_accountant_id' => $accId1,
            'issued_by_accountant_id' => $accId1,
            'issued_at' => '2026-10-25 09:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('debitnotes')->updateOrInsert(['debit_note_code' => $dn10Code], $dn10Record);

        // Chi tiết hóa đơn tháng 10
        $uf10Id = $utilityFees['NEX-10'] ?? null;
        $items10 = [
            ['cat' => 'RENT', 'desc' => 'Tiền thuê mặt bằng văn phòng Tháng 10/2026', 'qty' => 200.00, 'unit' => 'm2', 'price' => 1500000.00, 'amount' => $rentLine],
            ['cat' => 'SERVICE_FEE', 'desc' => 'Phí dịch vụ quản lý tòa nhà Tháng 10/2026', 'qty' => 200.00, 'unit' => 'm2', 'price' => 200000.00, 'amount' => $serviceLine],
            ['cat' => 'ELECTRICITY', 'desc' => 'Tiền điện lạnh & ánh sáng (Chỉ số: 12000 -> 15500)', 'qty' => 3500.00, 'unit' => 'kWh', 'price' => 4500.00, 'amount' => $elecLine],
            ['cat' => 'WATER', 'desc' => 'Tiền nước sinh hoạt (Chỉ số: 150 -> 210)', 'qty' => 60.00, 'unit' => 'm3', 'price' => 25000.00, 'amount' => $waterLine],
            ['cat' => 'MOTORBIKE_PARKING', 'desc' => 'Phí đỗ xe máy tháng (35 xe)', 'qty' => 35.00, 'unit' => 'chiếc', 'price' => 500000.00, 'amount' => $motorLine],
            ['cat' => 'CAR_PARKING', 'desc' => 'Phí đỗ xe ô tô tháng (04 xe cố định)', 'qty' => 4.00, 'unit' => 'chiếc', 'price' => 6300000.00, 'amount' => $carLine],
            ['cat' => 'OVERTIME', 'desc' => 'Phí sử dụng điều hòa làm việc ngoài giờ', 'qty' => 15.00, 'unit' => 'giờ', 'price' => 350000.00, 'amount' => $otLine],
        ];

        foreach ($items10 as $it) {
            DB::table('debitnote_detail')->updateOrInsert(
                [
                    'debit_note_id' => $dn10Id,
                    'item_category' => $it['cat'],
                ],
                [
                    'unit_id' => $unitId,
                    'utility_fee_id' => $uf10Id,
                    'description' => $it['desc'],
                    'quantity' => $it['qty'],
                    'unit_name' => $it['unit'],
                    'unit_price' => $it['price'],
                    'line_amount' => $it['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Hóa đơn Tháng 11/2026 - VỪA PHÁT HÀNH ĐANG CHỜ THANH TOÁN (ISSUED)
        $dn11Id = '0192e220-0050-7000-8000-000000000002';
        $dn11Code = 'DN-202611-0001';

        $elecLine11  = 14850000.00; // 3300 kWh x 4500đ
        $waterLine11 = 1375000.00;  // 55 m3 x 25000đ
        $otLine11    = 3500000.00;   // 10 giờ x 350k

        $subTotal11 = $rentLine + $serviceLine + $elecLine11 + $waterLine11 + $motorLine + $carLine + $otLine11; // 402.425.000đ
        $vat11      = $subTotal11 * 0.10; // 40.242.500đ
        $total11    = $subTotal11 + $vat11; // 442.667.500đ

        $dn11Record = [
            'id' => $dn11Id,
            'debit_note_code' => $dn11Code,
            'building_id' => $buildingId,
            'tenant_id' => $tenantId,
            'contract_id' => $contractId,
            'billing_year' => 2026,
            'billing_month' => 11,
            'note_type' => 'REGULAR',
            'issue_date' => '2026-11-01',
            'due_date' => '2026-11-15',
            'sub_total_amount' => $subTotal11,
            'vat_percent' => 10.00,
            'previous_debt_amount' => 0.00,
            'late_fee_amount' => 0.00,
            'total_amount' => $total11,
            'paid_amount' => 0.00,
            'vietqr_payload' => '00020101021238540010A000000727012400069704360114007100123456752046011530370454094426675005802VN62190815DN20261100016304E8A9',
            'is_immutable' => true,
            'status' => 'ISSUED',
            'created_by_accountant_id' => $accId1,
            'issued_by_accountant_id' => $accId1,
            'issued_at' => '2026-11-01 08:30:00',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('debitnotes')->updateOrInsert(['debit_note_code' => $dn11Code], $dn11Record);

        $uf11Id = $utilityFees['NEX-11'] ?? null;
        $items11 = [
            ['cat' => 'RENT', 'desc' => 'Tiền thuê mặt bằng văn phòng Tháng 11/2026', 'qty' => 200.00, 'unit' => 'm2', 'price' => 1500000.00, 'amount' => $rentLine],
            ['cat' => 'SERVICE_FEE', 'desc' => 'Phí dịch vụ quản lý tòa nhà Tháng 11/2026', 'qty' => 200.00, 'unit' => 'm2', 'price' => 200000.00, 'amount' => $serviceLine],
            ['cat' => 'ELECTRICITY', 'desc' => 'Tiền điện lạnh & chiếu sáng (Chỉ số: 15500 -> 18800)', 'qty' => 3300.00, 'unit' => 'kWh', 'price' => 4500.00, 'amount' => $elecLine11],
            ['cat' => 'WATER', 'desc' => 'Tiền nước sinh hoạt (Chỉ số: 210 -> 265)', 'qty' => 55.00, 'unit' => 'm3', 'price' => 25000.00, 'amount' => $waterLine11],
            ['cat' => 'MOTORBIKE_PARKING', 'desc' => 'Phí đỗ xe máy tháng (35 xe)', 'qty' => 35.00, 'unit' => 'chiếc', 'price' => 500000.00, 'amount' => $motorLine],
            ['cat' => 'CAR_PARKING', 'desc' => 'Phí đỗ xe ô tô tháng (04 xe cố định)', 'qty' => 4.00, 'unit' => 'chiếc', 'price' => 6300000.00, 'amount' => $carLine],
            ['cat' => 'OVERTIME', 'desc' => 'Phí sử dụng điều hòa làm việc ngoài giờ', 'qty' => 10.00, 'unit' => 'giờ', 'price' => 350000.00, 'amount' => $otLine11],
        ];

        foreach ($items11 as $it) {
            DB::table('debitnote_detail')->updateOrInsert(
                [
                    'debit_note_id' => $dn11Id,
                    'item_category' => $it['cat'],
                ],
                [
                    'unit_id' => $unitId,
                    'utility_fee_id' => $uf11Id,
                    'description' => $it['desc'],
                    'quantity' => $it['qty'],
                    'unit_name' => $it['unit'],
                    'unit_price' => $it['price'],
                    'line_amount' => $it['amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return [
            'DN-10' => [
                'id' => $dn10Id,
                'building_id' => $buildingId,
                'tenant_id' => $tenantId,
                'amount' => $total10,
            ],
            'DN-11' => [
                'id' => $dn11Id,
                'building_id' => $buildingId,
                'tenant_id' => $tenantId,
                'amount' => $total11,
            ],
        ];
    }

    private function seedPaymentTransactions(array $debitNotes, array $accList): void
    {
        $accId1 = $accList['NV-KT-001'];
        $dn10 = $debitNotes['DN-10'] ?? null;

        if (!$dn10) return;

        $txnCode = 'TXN-202611-0001';
        $txnRecord = [
            'id' => '0192e220-0060-7000-8000-000000000001',
            'transaction_code' => $txnCode,
            'building_id' => $dn10['building_id'],
            'tenant_id' => $dn10['tenant_id'],
            'debit_note_id' => $dn10['id'],
            'payment_method' => 'VIETQR',
            'amount_paid' => $dn10['amount'],
            'bank_reference_number' => 'FT26305882910482',
            'proof_file_private_path' => 'payments/' . $txnCode . '/vietqr_proof.png',
            'paid_at' => '2026-11-02 14:22:15',
            'verification_status' => 'APPROVED',
            'verified_by_accountant_id' => $accId1,
            'verified_at' => '2026-11-02 14:35:00',
            'accountant_note' => 'Đã khớp sao kê Vietcombank tự động qua Webhook Napas VietQR',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('payment_transactions')->updateOrInsert(['transaction_code' => $txnCode], $txnRecord);
    }

    private function seedLeadsAndActivities(array $buildings, array $salesList, array $tenants): void
    {
        $salesId1 = $salesList['NV-SALE-001'];
        $salesId2 = $salesList['NV-SALE-002'];
        $salesId3 = $salesList['NV-SALE-003'];

        $nexusId = $buildings['NEXUS-TOWER'];
        $sgc2Id  = $buildings['SAIGON-CENTRE-2'];
        $pearlId = $buildings['PEARL-PLAZA'];

        $leads = [
            [
                'id' => '0192e220-0070-7000-8000-000000000001',
                'contact_full_name' => 'Nguyễn Anh Tuấn',
                'company_name' => 'Tập Đoàn Công Nghệ Sun Asterisk',
                'phone' => '0909123888',
                'email' => 'tuan.nguyen@sun-asterisk.com',
                'lead_source' => 'WEBSITE',
                'building_id' => $nexusId,
                'desired_area_sqm' => 500.00,
                'budget_min' => 600000000.00,
                'budget_max' => 800000000.00,
                'preferred_site_tour_at' => '2026-11-08 09:30:00',
                'customer_note' => 'Cần tìm văn phòng hạng A view sông cho 120 kỹ sư phần mềm, cần bàn giao thô hoặc cơ bản.',
                'assigned_sales_id' => $salesId1,
                'assigned_at' => '2026-11-04 10:00:00',
                'sla_deadline_at' => '2026-11-04 12:00:00',
                'first_contacted_at' => '2026-11-04 10:45:00',
                'status' => 'NEGOTIATING',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '0192e220-0070-7000-8000-000000000002',
                'contact_full_name' => 'Trần Mỹ Dung',
                'company_name' => 'Công Ty Luật Quốc Tế Baker & McKenzie',
                'phone' => '0938765432',
                'email' => 'dung.tran@bakermckenzie.com',
                'lead_source' => 'DIRECT_WALK_IN',
                'building_id' => $sgc2Id,
                'desired_area_sqm' => 300.00,
                'budget_min' => 400000000.00,
                'budget_max' => 500000000.00,
                'preferred_site_tour_at' => '2026-11-07 14:00:00',
                'customer_note' => 'Cần sàn hoàn thiện nội thất cao cấp sẵn sàng vào làm việc ngay.',
                'assigned_sales_id' => $salesId2,
                'assigned_at' => '2026-11-05 08:30:00',
                'sla_deadline_at' => '2026-11-05 10:30:00',
                'first_contacted_at' => '2026-11-05 09:15:00',
                'status' => 'NEW',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '0192e220-0070-7000-8000-000000000003',
                'contact_full_name' => 'Lê Thành Đạt',
                'company_name' => 'Công Ty Logistics Bee VN',
                'phone' => '0987112233',
                'email' => 'dat.le@beelogistics.com',
                'lead_source' => 'GOOGLE_ADS',
                'building_id' => $pearlId,
                'desired_area_sqm' => 150.00,
                'budget_min' => 90000000.00,
                'budget_max' => 120000000.00,
                'customer_note' => 'Tìm sàn gần cầu Sài Gòn tiện di chuyển ra cảng Cát Lái.',
                'assigned_sales_id' => $salesId3,
                'status' => 'PENDING_ASSIGN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($leads as $l) {
            DB::table('leads')->updateOrInsert(['id' => $l['id']], $l);
        }

        // Hoạt động chăm sóc lead 1
        $activities = [
            [
                'lead_id' => '0192e220-0070-7000-8000-000000000001',
                'sales_id' => $salesId1,
                'activity_type' => 'CALL',
                'title' => 'Gọi điện tư vấn ban đầu & xác nhận nhu cầu',
                'content' => 'Khách hàng quan tâm căn Lầu 20 The Nexus Tower. Đã trao đổi về đơn giá thuê 1.5 triệu/m2 và phí quản lý 200k/m2.',
                'event_at' => '2026-11-04 10:45:00',
                'created_at' => now(),
            ],
            [
                'lead_id' => '0192e220-0070-7000-8000-000000000001',
                'sales_id' => $salesId1,
                'activity_type' => 'SITE_TOUR',
                'title' => 'Đưa đón ban lãnh đạo khảo sát thực tế mặt bằng',
                'content' => 'Dẫn đoàn 3 người của Sun Asterisk khảo sát căn 500m2 lầu 20. Khách đánh giá cao view sông và sảnh lễ tân.',
                'event_at' => '2026-11-05 15:00:00',
                'created_at' => now(),
            ],
            [
                'lead_id' => '0192e220-0070-7000-8000-000000000001',
                'sales_id' => $salesId1,
                'activity_type' => 'QUOTATION_SENT',
                'title' => 'Gửi báo giá chính thức & dự thảo hợp đồng thuê 3 năm',
                'content' => 'Đã gửi file PDF bảng tính chi phí thuê + tiền cọc 3 tháng qua email cho Giám đốc Tài chính.',
                'event_at' => '2026-11-05 17:30:00',
                'created_at' => now(),
            ],
        ];

        foreach ($activities as $act) {
            DB::table('lead_activities')->insertOrIgnore($act);
        }
    }

    private function seedTicketsAndHistory(array $contracts, array $pmList): void
    {
        $pmId1 = $pmList['NV-BQL-001'];
        $pmId2 = $pmList['NV-BQL-002'];

        $nexusContract = $contracts['HD-2026/01-NEXUS'] ?? null;
        $sgcContract   = $contracts['HD-2026/02-SGC2'] ?? null;

        if (!$nexusContract) return;

        $ticket1Id = '0192e220-0080-7000-8000-000000000001';
        $ticket1Code = 'TK-202611-0001';

        $ticket1 = [
            'id' => $ticket1Id,
            'ticket_code' => $ticket1Code,
            'building_id' => $nexusContract['building_id'],
            'creator_role' => 'TENANT',
            'creator_tenant_id' => $nexusContract['tenant_id'],
            'tenant_id' => $nexusContract['tenant_id'],
            'contract_id' => $nexusContract['contract_id'],
            'unit_id' => $nexusContract['unit_id'],
            'category' => 'MAINTENANCE',
            'priority' => 'HIGH',
            'title' => 'Kiểm tra bảo dưỡng điều hòa không mát khu vực phòng máy chủ Server',
            'description' => 'Hệ thống lạnh khu vực Server Room nhiệt độ tăng lên 28 độ C, cần kỹ thuật viên xử lý gấp trong ngày.',
            'assigned_pm_id' => $pmId1,
            'status' => 'IN_PROGRESS',
            'expected_completion_at' => '2026-11-06 17:00:00',
            'created_at' => '2026-11-06 08:30:00',
            'updated_at' => now(),
        ];

        DB::table('tickets')->updateOrInsert(['ticket_code' => $ticket1Code], $ticket1);

        // Lịch sử ticket 1
        $histories1 = [
            [
                'ticket_id' => $ticket1Id,
                'actor_type' => 'TENANT',
                'tenant_actor_id' => $nexusContract['tenant_id'],
                'pm_actor_id' => null,
                'from_status' => null,
                'to_status' => 'NEW',
                'message' => 'Khách thuê VNG tạo yêu cầu xử lý khẩn cấp hệ thống lạnh phòng Server.',
                'created_at' => '2026-11-06 08:30:00',
            ],
            [
                'ticket_id' => $ticket1Id,
                'actor_type' => 'PM',
                'tenant_actor_id' => null,
                'pm_actor_id' => $pmId1,
                'from_status' => 'NEW',
                'to_status' => 'IN_PROGRESS',
                'message' => 'Kỹ thuật viên Đỗ Minh Quang đã tiếp nhận, đang cùng đội bảo trì Daikin kiểm tra van điều áp tầng 8.',
                'created_at' => '2026-11-06 09:15:00',
            ],
        ];

        foreach ($histories1 as $h) {
            DB::table('ticket_history')->insertOrIgnore($h);
        }

        if ($sgcContract) {
            $ticket2Id = '0192e220-0080-7000-8000-000000000002';
            $ticket2Code = 'TK-202611-0002';

            $ticket2 = [
                'id' => $ticket2Id,
                'ticket_code' => $ticket2Code,
                'building_id' => $sgcContract['building_id'],
                'creator_role' => 'TENANT',
                'creator_tenant_id' => $sgcContract['tenant_id'],
                'tenant_id' => $sgcContract['tenant_id'],
                'contract_id' => $sgcContract['contract_id'],
                'unit_id' => $sgcContract['unit_id'],
                'category' => 'OVERTIME_CONSTRUCTION',
                'priority' => 'NORMAL',
                'title' => 'Đăng ký thi công lắp đặt thêm vách kính phòng họp thứ 7 tuần này',
                'description' => 'Công ty Shopee đăng ký cho nhà thầu nội thất vào thi công từ 13h00 đến 18h00 Thứ 7 ngày 07/11/2026.',
                'assigned_pm_id' => $pmId2,
                'status' => 'COMPLETED',
                'resolution_summary' => 'Đã duyệt phương án PCCC và cấp thẻ nhà thầu ra vào tòa nhà.',
                'resolved_at' => '2026-11-05 16:00:00',
                'completed_at' => '2026-11-05 16:30:00',
                'tenant_rating' => 5,
                'tenant_feedback' => 'Ban quản lý hỗ trợ rất nhanh và chu đáo!',
                'created_at' => '2026-11-05 10:00:00',
                'updated_at' => now(),
            ];

            DB::table('tickets')->updateOrInsert(['ticket_code' => $ticket2Code], $ticket2);
        }
    }
}
