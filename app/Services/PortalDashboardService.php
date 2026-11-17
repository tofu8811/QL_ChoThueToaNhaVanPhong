<?php

namespace App\Services;

use App\Enums\DebitNoteStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UnitStatus;
use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\DebitNote;
use App\Models\Lead;
use App\Models\PropertyManager;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Service xử lý toàn bộ nghiệp vụ thống kê Dashboard, chuẩn bị dữ liệu Portal,
 * tính toán dự toán báo giá và kết xuất báo cáo CSV (Business Logic Layer - BLL).
 */
class PortalDashboardService
{
    /**
     * Tổng hợp toàn bộ dữ liệu hiển thị cho Cổng thông tin (Public Portal) và Quản trị BMS (Dashboard).
     * Bọc try-catch đầy đủ và cung cấp giá trị fallback an toàn khi CSDL gặp sự cố.
     *
     * @return array<string, mixed>
     */
    public function getDashboardPortalData(): array
    {
        $buildings = collect();
        $provinces = collect();
        $recentContracts = collect();
        $recentDebitNotes = collect();
        $recentTickets = collect();
        $leads = collect();
        $sampleTenant = null;
        $sampleContract = null;
        $sampleDebit = null;
        $serverStaff = [
            'admins' => collect(),
            'sales' => collect(),
            'pm' => collect(),
            'accountants' => collect(),
        ];
        $defaultStaffToken = '';

        $stats = [
            'total_buildings' => 0,
            'total_leasable_area' => 0,
            'total_tenants' => 0,
            'available_units' => 0,
            'total_units' => 0,
            'rented_units' => 0,
            'expiring_units' => 0,
            'rented_area' => 0.0,
            'available_area' => 0.0,
            'expiring_area' => 0.0,
            'total_units_area' => 0.0,
            'rented_pct' => 0.0,
            'available_pct' => 0.0,
            'expiring_pct' => 0.0,
            'offset_avail' => 0.0,
            'offset_exp' => 0.0,
            'billing_total' => 0.0,
            'billing_paid' => 0.0,
            'billing_pending' => 0.0,
            'billing_overdue' => 0.0,
            'billing_paid_count' => 0,
            'billing_overdue_count' => 0,
            'billing_paid_pct' => 0.0,
            'billing_pending_pct' => 0.0,
            'billing_overdue_pct' => 0.0,
            'open_tickets' => 0,
            'building_performances' => [],
            'first_ticket_priority_label' => TicketPriority::HIGH->label(),
            'first_ticket_status_label' => TicketStatus::IN_PROGRESS->label(),
        ];

        $exchangeRate = (float) config('services.bms.usd_exchange_rate', 25000);

        try {
            if (Schema::hasTable('buildings')) {
                $stats['total_buildings'] = Building::published()->count();
                $stats['total_leasable_area'] = (float) Building::published()->sum('total_leasable_area_sqm');

                if (Schema::hasTable('units')) {
                    $stats['available_units'] = Unit::where('status', UnitStatus::AVAILABLE)->count();
                }

                if (Schema::hasTable('tenants')) {
                    $stats['total_tenants'] = Tenant::count();
                }

                $buildings = Building::published()
                    ->with(['primaryImage', 'images', 'units' => function ($q) {
                        $q->whereIn('status', [UnitStatus::AVAILABLE, UnitStatus::EXPIRING_SOON]);
                    }])
                    ->withCount([
                        'units as available_units_count' => function ($q) {
                            $q->whereIn('status', [UnitStatus::AVAILABLE, UnitStatus::EXPIRING_SOON]);
                        },
                    ])
                    ->latest()
                    ->get()
                    ->map(function (Building $b) use ($exchangeRate) {
                        $primaryImg = $b->primaryImage?->image_url
                            ?? $b->images->first()?->image_url
                            ?? config('services.bms.default_building_image', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80');

                        $buildingImages = $b->images->pluck('image_url')->toArray();
                        if (empty($buildingImages) && $primaryImg) {
                            $buildingImages = [$primaryImg];
                        }

                        $floors = $b->units->map(function (Unit $unit) {
                            $unitStatus = $unit->status instanceof UnitStatus ? $unit->status : UnitStatus::tryFrom((string) $unit->status);

                            return [
                                'floor' => 'Tầng ' . ($unit->floor_number ?? 1),
                                'area' => number_format((float) ($unit->area_sqm ?? 100), 0, ',', '.') . ' m²',
                                'deliver' => $unit->handover_condition ?? 'Hoàn thiện cơ bản',
                                'price' => $unit->formatted_rent_price,
                                'status' => $unitStatus?->label() ?? UnitStatus::AVAILABLE->label(),
                            ];
                        })->toArray();

                        $minUsd = $b->base_rent_price_usd > 0 ? $b->base_rent_price_usd : 25;
                        $maxUsd = $b->max_rent_price_per_sqm
                            ? (int) round(((float) $b->max_rent_price_per_sqm) / $exchangeRate)
                            : ($minUsd + 10);

                        return [
                            'id' => $b->id,
                            'name' => $b->name,
                            'slug' => $b->slug ?? Str::slug($b->name),
                            'grade' => 'Hạng ' . ($b->grade ?? 'A'),
                            'district' => $b->district ?? '',
                            'address' => $b->address . ($b->ward ? ', ' . $b->ward : '') . ($b->district ? ', ' . $b->district : ''),
                            'priceUsd' => $minUsd,
                            'priceMaxUsd' => $maxUsd,
                            'priceVnd' => number_format((float) ($b->min_rent_price_per_sqm ?: 600000), 0, ',', '.') . ' đ',
                            'totalArea' => $b->formatted_area,
                            'availableArea' => number_format((float) $b->units->sum('area_sqm'), 0, ',', '.') . ' m²',
                            'floorPlate' => '1.000 m²',
                            'structure' => ($b->total_floors ?: 1) . ' Tầng nổi - ' . ($b->total_basements ?: 0) . ' Hầm',
                            'status' => UnitStatus::AVAILABLE->label(),
                            'statusClass' => 'empty',
                            'image' => $primaryImg,
                            'images' => $buildingImages,
                            'floors' => $floors,
                        ];
                    });
            }

            if (Schema::hasTable('provinces')) {
                $provinces = Province::orderBy('name')->get();
            }

            if (Schema::hasTable('contracts')) {
                $recentContracts = Contract::with(['tenant', 'building', 'units'])->latest()->take(10)->get();
            }

            if (Schema::hasTable('debitnotes')) {
                $recentDebitNotes = DebitNote::with(['tenant', 'building'])->latest()->take(10)->get();
                $totalBilling = (float) DebitNote::sum('total_amount');
                $paidBilling = (float) DebitNote::where('status', DebitNoteStatus::PAID)->sum('total_amount');
                $pendingBilling = (float) DebitNote::whereIn('status', [
                    DebitNoteStatus::ISSUED,
                    DebitNoteStatus::PARTIALLY_PAID,
                    DebitNoteStatus::DRAFT,
                ])->sum('total_amount');
                $overdueBilling = (float) DebitNote::where('status', DebitNoteStatus::OVERDUE)->sum('total_amount');

                $stats['billing_total'] = $totalBilling;
                $stats['billing_paid'] = $paidBilling;
                $stats['billing_pending'] = $pendingBilling;
                $stats['billing_overdue'] = $overdueBilling;
                $stats['billing_paid_count'] = DebitNote::where('status', DebitNoteStatus::PAID)->count();
                $stats['billing_overdue_count'] = DebitNote::where('status', DebitNoteStatus::OVERDUE)->count();
                $stats['billing_paid_pct'] = $totalBilling > 0 ? round(($paidBilling / $totalBilling) * 100, 1) : 0.0;
                $stats['billing_pending_pct'] = $totalBilling > 0 ? round(($pendingBilling / $totalBilling) * 100, 1) : 0.0;
                $stats['billing_overdue_pct'] = $totalBilling > 0 ? round(($overdueBilling / $totalBilling) * 100, 1) : 0.0;
            }

            if (Schema::hasTable('leads')) {
                $leads = Lead::with('building')->latest()->take(15)->get();
            }

            if (Schema::hasTable('tickets')) {
                $stats['open_tickets'] = Ticket::whereIn('status', [
                    TicketStatus::NEW,
                    TicketStatus::IN_PROGRESS,
                    TicketStatus::ON_HOLD,
                ])->count();
                $recentTickets = Ticket::with(['building', 'unit', 'tenant', 'assignedPm'])->latest()->take(5)->get();
            }

            if (Schema::hasTable('units')) {
                $totalUnitsCount = Unit::count();
                $leasedUnitsCount = Unit::where('status', UnitStatus::LEASED)->count();
                $availUnitsCount = Unit::where('status', UnitStatus::AVAILABLE)->count();
                $expUnitsCount = Unit::whereIn('status', [
                    UnitStatus::EXPIRING_SOON,
                    UnitStatus::MAINTENANCE,
                    UnitStatus::RESERVED,
                ])->count();

                $leasedArea = (float) Unit::where('status', UnitStatus::LEASED)->sum('area_sqm');
                $availArea = (float) Unit::where('status', UnitStatus::AVAILABLE)->sum('area_sqm');
                $expArea = (float) Unit::whereIn('status', [
                    UnitStatus::EXPIRING_SOON,
                    UnitStatus::MAINTENANCE,
                    UnitStatus::RESERVED,
                ])->sum('area_sqm');
                $totalUnitsArea = $leasedArea + $availArea + $expArea;

                if ($totalUnitsCount > 0) {
                    $stats['total_units'] = $totalUnitsCount;
                    $stats['rented_units'] = $leasedUnitsCount;
                    $stats['available_units'] = $availUnitsCount;
                    $stats['expiring_units'] = $expUnitsCount;

                    $stats['rented_area'] = $leasedArea;
                    $stats['available_area'] = $availArea;
                    $stats['expiring_area'] = $expArea;
                    $stats['total_units_area'] = $totalUnitsArea;

                    $stats['rented_pct'] = round(($leasedUnitsCount / $totalUnitsCount) * 100, 1);
                    $stats['available_pct'] = round(($availUnitsCount / $totalUnitsCount) * 100, 1);
                    $stats['expiring_pct'] = round(max(0, 100 - $stats['rented_pct'] - $stats['available_pct']), 1);
                    $stats['offset_avail'] = -$stats['rented_pct'];
                    $stats['offset_exp'] = -($stats['rented_pct'] + $stats['available_pct']);
                }

                $stats['building_performances'] = Building::published()
                    ->withCount([
                        'units as total_u',
                        'units as leased_u' => fn ($q) => $q->where('status', UnitStatus::LEASED),
                    ])
                    ->having('total_u', '>', 0)
                    ->orderByDesc('leased_u')
                    ->take(4)
                    ->get()
                    ->map(function (Building $b) {
                        $pct = $b->total_u > 0 ? round(($b->leased_u / $b->total_u) * 100, 1) : 0.0;
                        $barColor = $pct >= 80 ? '#10b981' : ($pct >= 40 ? '#2563eb' : '#f59e0b');

                        return [
                            'id' => $b->id,
                            'name' => $b->name,
                            'district' => $b->district ?? '',
                            'total_units' => $b->total_u,
                            'leased_units' => $b->leased_u,
                            'available_units' => max(0, $b->total_u - $b->leased_u),
                            'occupancy_pct' => $pct,
                            'bar_color' => $barColor,
                        ];
                    })->toArray();
            }

            if (Schema::hasTable('tenants')) {
                $sampleTenant = Tenant::with(['contracts.building', 'debitNotes.building'])->first();
            }
            $sampleContract = $sampleTenant?->contracts?->first() ?? $recentContracts->first();
            $sampleDebit = $sampleTenant?->debitNotes?->first() ?? $recentDebitNotes->first();

            $serverStaff = [
                'admins' => Schema::hasTable('admins') ? Admin::latest()->get() : collect(),
                'sales' => Schema::hasTable('sales') ? Sale::with('buildings')->latest()->get() : collect(),
                'pm' => Schema::hasTable('property_managers') ? PropertyManager::with('buildings')->latest()->get() : collect(),
                'accountants' => Schema::hasTable('accountants') ? Accountant::with('buildings')->latest()->get() : collect(),
            ];

            if (Schema::hasTable('admins')) {
                $superAdmin = Admin::where('is_super_admin', true)->first();
                if ($superAdmin) {
                    $superAdmin->tokens()->where('name', 'portal-admin-token')->delete();
                    $defaultStaffToken = $superAdmin->createToken('portal-admin-token')->plainTextToken;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tổng hợp dữ liệu Portal & BMS Dashboard tại PortalDashboardService', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        $firstContract = $recentContracts->first();
        $pendingDebit = $recentDebitNotes->first(fn (DebitNote $dn) => $dn->status !== DebitNoteStatus::PAID)
            ?? $recentDebitNotes->first();
        $firstTicket = $recentTickets->first();

        if ($firstTicket) {
            $priorityEnum = $firstTicket->priority instanceof TicketPriority
                ? $firstTicket->priority
                : TicketPriority::tryFrom((string) $firstTicket->priority);
            $statusEnum = $firstTicket->status instanceof TicketStatus
                ? $firstTicket->status
                : TicketStatus::tryFrom((string) $firstTicket->status);

            $stats['first_ticket_priority_label'] = $priorityEnum?->label() ?? TicketPriority::HIGH->label();
            $stats['first_ticket_status_label'] = $statusEnum?->label() ?? TicketStatus::IN_PROGRESS->label();
        }

        $firstBuildingPriceUsd = (float) ($buildings->first()['priceUsd'] ?? 30);
        $firstBuildingName = (string) ($buildings->first()['name'] ?? 'Tòa nhà văn phòng');

        $notifications = $this->buildSystemNotifications($recentTickets, $recentDebitNotes, $leads, (int) ($stats['expiring_units'] ?? 0));
        $stats['unread_notifications_count'] = count($notifications);

        return [
            'serverBuildings' => $buildings,
            'provinces' => $provinces,
            'recentContracts' => $recentContracts,
            'recentDebitNotes' => $recentDebitNotes,
            'recentTickets' => $recentTickets,
            'leads' => $leads,
            'notifications' => $notifications,
            'firstContract' => $firstContract,
            'pendingDebit' => $pendingDebit,
            'firstTicket' => $firstTicket,
            'defaultQuoteEstimate' => $this->calculateQuoteEstimate($firstBuildingPriceUsd, 200, 6, 3, $firstBuildingName),
            'sampleTenant' => $sampleTenant,
            'sampleContract' => $sampleContract,
            'sampleDebit' => $sampleDebit,
            'serverStaff' => $serverStaff,
            'defaultStaffToken' => $defaultStaffToken,
            'stats' => $stats,
        ];
    }

    /**
     * Xây dựng danh sách thông báo vận hành thời gian thực từ các phân hệ Bảo trì, Công nợ, CRM và Mặt bằng.
     *
     * @param  \Illuminate\Support\Collection<int, Ticket>  $tickets
     * @param  \Illuminate\Support\Collection<int, DebitNote>  $debitNotes
     * @param  \Illuminate\Support\Collection<int, Lead>  $leads
     * @param  int  $expiringUnitsCount
     * @return list<array<string, string>>
     */
    protected function buildSystemNotifications($tickets, $debitNotes, $leads, int $expiringUnitsCount): array
    {
        $items = [];

        try {
            // 1. Cảnh báo Ticket bảo trì kỹ thuật đang mở
            foreach ($tickets as $tk) {
                $statusEnum = $tk->status instanceof TicketStatus
                    ? $tk->status
                    : TicketStatus::tryFrom((string) $tk->status);

                if ($statusEnum && !$statusEnum->isOpen()) {
                    continue;
                }

                $priorityEnum = $tk->priority instanceof TicketPriority
                    ? $tk->priority
                    : TicketPriority::tryFrom((string) $tk->priority);

                $items[] = [
                    'type' => 'ticket',
                    'icon' => 'fa-solid fa-screwdriver-wrench',
                    'icon_bg' => '#fef2f2',
                    'icon_color' => '#e11d48',
                    'badge_text' => 'Bảo trì • ' . ($priorityEnum?->label() ?? TicketPriority::HIGH->label()),
                    'badge_style' => 'background:#fef2f2;color:#e11d48;',
                    'title' => $tk->title ?? 'Yêu cầu bảo trì kỹ thuật',
                    'subtitle' => ($tk->ticket_code ?? 'Ticket') . ' • ' . ($tk->building?->name ?? 'Tòa nhà văn phòng'),
                    'target_tab' => 'tab-admin-tickets',
                    'nav_id' => 'nav-item-tickets',
                ];
            }

            // 2. Cảnh báo Phiếu thu (Debit Note) chờ thanh toán hoặc quá hạn
            foreach ($debitNotes as $dn) {
                if ($dn->status === DebitNoteStatus::PAID || $dn->status === DebitNoteStatus::CANCELLED) {
                    continue;
                }

                $isOverdue = ($dn->status === DebitNoteStatus::OVERDUE);
                $items[] = [
                    'type' => 'billing',
                    'icon' => 'fa-solid fa-file-invoice-dollar',
                    'icon_bg' => $isOverdue ? '#fef2f2' : '#fef3c7',
                    'icon_color' => $isOverdue ? '#e11d48' : '#d97706',
                    'badge_text' => 'Công nợ • ' . $dn->status_label,
                    'badge_style' => $isOverdue ? 'background:#fef2f2;color:#e11d48;' : 'background:#fef3c7;color:#b45309;',
                    'title' => ($dn->tenant?->company_name ?? 'Khách thuê') . ' (' . number_format($dn->remaining_debt_amount, 0, ',', '.') . ' đ)',
                    'subtitle' => ($dn->debit_note_code ?? 'DN') . ' • Hạn đóng: ' . ($dn->due_date?->format('d/m/Y') ?? 'Trong kỳ'),
                    'target_tab' => 'tab-admin-billing',
                    'nav_id' => 'nav-item-billing',
                ];
            }

            // 3. Thông báo Leads khách hàng mới trên CRM Pipeline (lấy tối đa 3 lead mới nhất)
            foreach ($leads->take(3) as $lead) {
                $leadName = $lead->company_name ?: ($lead->contact_full_name ?? 'Khách hàng tiềm năng');
                $areaText = $lead->desired_area_sqm ? number_format((float) $lead->desired_area_sqm, 0, ',', '.') . ' m²' : '';

                $items[] = [
                    'type' => 'lead',
                    'icon' => 'fa-solid fa-filter-circle-dollar',
                    'icon_bg' => '#eff6ff',
                    'icon_color' => '#2563eb',
                    'badge_text' => 'Lead CRM mới',
                    'badge_style' => 'background:#eff6ff;color:#1d4ed8;',
                    'title' => $leadName . ($areaText ? " — Nhu cầu {$areaText}" : ''),
                    'subtitle' => ($lead->building?->name ?? 'Quan tâm thuê sàn') . ' • SĐT: ' . ($lead->phone ?? '---'),
                    'target_tab' => 'tab-sales-leads',
                    'nav_id' => 'nav-item-leads',
                ];
            }

            // 4. Cảnh báo mặt bằng sắp hết hạn hợp đồng / bảo trì
            if ($expiringUnitsCount > 0) {
                $items[] = [
                    'type' => 'unit',
                    'icon' => 'fa-solid fa-building-circle-exclamation',
                    'icon_bg' => '#f0fdf4',
                    'icon_color' => '#16a34a',
                    'badge_text' => 'Mặt bằng chú ý',
                    'badge_style' => 'background:#f0fdf4;color:#15803d;',
                    'title' => "Có {$expiringUnitsCount} mặt bằng sắp hết hạn / đang bảo trì",
                    'subtitle' => 'Bấm để kiểm tra danh sách tòa nhà & trạng thái mặt bằng',
                    'target_tab' => 'tab-admin-buildings',
                    'nav_id' => 'nav-item-buildings',
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tổng hợp danh sách thông báo vận hành tại PortalDashboardService', [
                'message' => $e->getMessage(),
            ]);
        }

        return $items;
    }

    /**
     * Tính toán dự toán chi phí thuê văn phòng (Base Rent + Service Fee + VAT 10% + Tiền đặt cọc).
     * Công thức chuẩn BLL: Thuế GTGT 10% áp dụng trên tổng tiền thuê cơ sở và phí dịch vụ quản lý.
     *
     * @param  float  $priceUsd  Đơn giá thuê cơ sở (USD/m2/tháng)
     * @param  float  $areaSqm  Diện tích thuê dự kiến (m2)
     * @param  float  $serviceFeeUsd  Phí quản lý dịch vụ (USD/m2/tháng)
     * @param  int  $depositMonths  Số tháng đặt cọc bảo đảm hợp đồng
     * @param  string  $buildingName  Tên thương mại tòa nhà
     * @return array<string, mixed>
     */
    public function calculateQuoteEstimate(
        float $priceUsd,
        float $areaSqm,
        float $serviceFeeUsd,
        int $depositMonths,
        string $buildingName = 'Tòa nhà văn phòng'
    ): array {
        $exchangeRate = (float) config('services.bms.usd_exchange_rate', 25000);
        $vatRate = (float) config('services.bms.vat_rate', 0.10);

        $priceUsd = max(0, $priceUsd);
        $areaSqm = max(0, $areaSqm);
        $serviceFeeUsd = max(0, $serviceFeeUsd);
        $depositMonths = max(1, $depositMonths);

        $baseRentUsd = round($priceUsd * $areaSqm, 2);
        $baseRentVnd = round($baseRentUsd * $exchangeRate);

        $serviceTotalUsd = round($serviceFeeUsd * $areaSqm, 2);
        $serviceTotalVnd = round($serviceTotalUsd * $exchangeRate);

        $subTotalUsd = $baseRentUsd + $serviceTotalUsd;
        $vatUsd = round($subTotalUsd * $vatRate, 2);
        $vatVnd = round($vatUsd * $exchangeRate);

        $totalMonthlyUsd = round($subTotalUsd + $vatUsd, 2);
        $totalMonthlyVnd = round($totalMonthlyUsd * $exchangeRate);

        $depositUsd = round($baseRentUsd * $depositMonths, 2);
        $depositVnd = round($depositUsd * $exchangeRate);

        return [
            'building_name' => $buildingName,
            'area_sqm' => $areaSqm,
            'price_usd' => $priceUsd,
            'service_fee_usd' => $serviceFeeUsd,
            'deposit_months' => $depositMonths,
            'rent_formula' => '$' . number_format($priceUsd, 0) . '/m² × ' . number_format($areaSqm, 0, ',', '.') . ' m²',
            'rent_usd_formatted' => '$' . number_format($baseRentUsd, 0),
            'rent_vnd_formatted' => number_format($baseRentVnd, 0, ',', '.') . ' đ',
            'service_formula' => '$' . number_format($serviceFeeUsd, 0) . '/m² × ' . number_format($areaSqm, 0, ',', '.') . ' m²',
            'service_usd_formatted' => '$' . number_format($serviceTotalUsd, 0),
            'service_vnd_formatted' => number_format($serviceTotalVnd, 0, ',', '.') . ' đ',
            'vat_usd_formatted' => '$' . number_format($vatUsd, 0),
            'vat_vnd_formatted' => number_format($vatVnd, 0, ',', '.') . ' đ',
            'total_usd_formatted' => '$' . number_format($totalMonthlyUsd, 0) . ' / tháng',
            'total_vnd_formatted' => number_format($totalMonthlyVnd, 0, ',', '.') . ' đ / tháng',
            'deposit_formula' => "{$depositMonths} tháng tiền thuê cơ sở",
            'deposit_usd_formatted' => '$' . number_format($depositUsd, 0),
            'deposit_vnd_formatted' => number_format($depositVnd, 0, ',', '.') . ' đ',
        ];
    }

    /**
     * Xây dựng mảng các dòng dữ liệu CSV cho Phiếu Dự Toán & Báo Giá Thuê Văn Phòng.
     *
     * @param  array<string, mixed>  $params
     * @return list<list<string>>
     */
    public function getQuoteCsvRows(array $params): array
    {
        $calc = $this->calculateQuoteEstimate(
            (float) ($params['price_usd'] ?? 30),
            (float) ($params['area_sqm'] ?? 200),
            (float) ($params['service_fee_usd'] ?? 6),
            (int) ($params['deposit_months'] ?? 3),
            (string) ($params['building_name'] ?? 'Tòa nhà văn phòng')
        );

        return [
            ['PHIẾU DỰ TOÁN & BÁO GIÁ THUÊ VĂN PHÒNG (OFFICE BMS)'],
            ['Tòa nhà', $calc['building_name']],
            ['Diện tích dự kiến', number_format($calc['area_sqm'], 0, ',', '.') . ' m²'],
            ['Ngày lập báo giá', now()->format('d/m/Y H:i')],
            [],
            ['Hạng mục', 'Công thức', 'Quy đổi USD', 'Thành tiền VNĐ'],
            ['1. Tiền thuê mặt bằng cơ sở', $calc['rent_formula'], $calc['rent_usd_formatted'], $calc['rent_vnd_formatted']],
            ['2. Phí dịch vụ quản lý', $calc['service_formula'], $calc['service_usd_formatted'], $calc['service_vnd_formatted']],
            ['3. Thuế GTGT (VAT 10%)', '10%', $calc['vat_usd_formatted'], $calc['vat_vnd_formatted']],
            ['TỔNG HÀNG THÁNG (GỒM VAT)', '', $calc['total_usd_formatted'], $calc['total_vnd_formatted']],
            ['4. Tiền đặt cọc bảo đảm', $calc['deposit_formula'], $calc['deposit_usd_formatted'], $calc['deposit_vnd_formatted']],
        ];
    }

    /**
     * Xây dựng mảng các dòng dữ liệu CSV cho Báo Cáo Tổng Quan Vận Hành & Kinh Doanh.
     * Bọc try-catch và ghi Log::error đầy đủ khi truy vấn CSDL.
     *
     * @return list<list<string>>
     */
    public function getDashboardCsvRows(): array
    {
        try {
            $totalBuildings = Building::published()->count();
            $totalArea = (float) Building::published()->sum('total_leasable_area_sqm');
            $totalUnits = Unit::count();
            $leasedUnits = Unit::where('status', UnitStatus::LEASED)->count();
            $availUnits = Unit::where('status', UnitStatus::AVAILABLE)->count();
            $expUnits = Unit::whereIn('status', [
                UnitStatus::EXPIRING_SOON,
                UnitStatus::MAINTENANCE,
                UnitStatus::RESERVED,
            ])->count();

            $leasedArea = (float) Unit::where('status', UnitStatus::LEASED)->sum('area_sqm');
            $availArea = (float) Unit::where('status', UnitStatus::AVAILABLE)->sum('area_sqm');
            $expArea = (float) Unit::whereIn('status', [
                UnitStatus::EXPIRING_SOON,
                UnitStatus::MAINTENANCE,
                UnitStatus::RESERVED,
            ])->sum('area_sqm');

            $rentedPct = $totalUnits > 0 ? round(($leasedUnits / $totalUnits) * 100, 1) : 0.0;
            $availPct = $totalUnits > 0 ? round(($availUnits / $totalUnits) * 100, 1) : 0.0;
            $expPct = round(max(0, 100 - $rentedPct - $availPct), 1);

            $totalContracts = Contract::count();
            $totalTenants = Tenant::count();
            $totalBilling = (float) DebitNote::sum('total_amount');
            $paidBilling = (float) DebitNote::where('status', DebitNoteStatus::PAID)->sum('total_amount');
            $paidPct = $totalBilling > 0 ? round(($paidBilling / $totalBilling) * 100, 1) : 0.0;
            $openTickets = Ticket::whereIn('status', [
                TicketStatus::NEW,
                TicketStatus::IN_PROGRESS,
                TicketStatus::ON_HOLD,
            ])->count();
            $totalLeads = Lead::count();

            $buildingPerfs = Building::published()
                ->withCount([
                    'units as total_u',
                    'units as leased_u' => fn ($q) => $q->where('status', UnitStatus::LEASED),
                ])
                ->having('total_u', '>', 0)
                ->orderByDesc('leased_u')
                ->get();

            $rows = [
                ['BÁO CÁO TỔNG QUAN VẬN HÀNH & KINH DOANH TÒA NHÀ (OFFICE BMS)'],
                ['Thời gian trích xuất (Server)', now()->format('d/m/Y H:i:s')],
                [],
                ['1. CHỈ SỐ TỔNG QUAN HỆ THỐNG', 'Giá trị', 'Chi tiết'],
                ['Tổng số tòa nhà quản lý', "{$totalBuildings} tòa nhà", 'Tổng diện tích: ' . number_format($totalArea, 0, ',', '.') . ' m²'],
                ['Tổng số mặt bằng (Units)', "{$totalUnits} mặt bằng", 'Tổng diện tích sàn cắt: ' . number_format($leasedArea + $availArea + $expArea, 0, ',', '.') . ' m²'],
                ['Mặt bằng đang cho thuê', "{$leasedUnits} căn ({$rentedPct}%)", number_format($leasedArea, 0, ',', '.') . ' m²'],
                ['Mặt bằng trống sẵn sàng', "{$availUnits} căn ({$availPct}%)", number_format($availArea, 0, ',', '.') . ' m²'],
                ['Mặt bằng sắp hết hạn / bảo trì', "{$expUnits} căn ({$expPct}%)", number_format($expArea, 0, ',', '.') . ' m²'],
                ['Hợp đồng đang quản lý', "{$totalContracts} hợp đồng", "{$totalTenants} doanh nghiệp thuê"],
                ['Tổng doanh thu kỳ hiện tại', number_format($totalBilling, 0, ',', '.') . ' VNĐ', 'Đã thu: ' . number_format($paidBilling, 0, ',', '.') . " VNĐ ({$paidPct}%)"],
                ['Ticket bảo trì đang mở', "{$openTickets} ticket", "Leads CRM: {$totalLeads} leads"],
                [],
                ['2. HIỆU SUẤT LẤP ĐẦY THEO TÒA NHÀ', 'Khu vực', 'Số sàn đã thuê / Tổng sàn', 'Tỷ lệ lấp đầy (%)'],
            ];

            foreach ($buildingPerfs as $b) {
                $pct = $b->total_u > 0 ? round(($b->leased_u / $b->total_u) * 100, 1) : 0.0;
                $rows[] = [$b->name, $b->district ?? '', "{$b->leased_u}/{$b->total_u} mặt bằng", "{$pct}%"];
            }

            return $rows;
        } catch (\Throwable $e) {
            Log::error('Lỗi khi trích xuất dữ liệu CSV Tổng quan tại PortalDashboardService', [
                'message' => $e->getMessage(),
            ]);

            return [
                ['BÁO CÁO TỔNG QUAN VẬN HÀNH & KINH DOANH TÒA NHÀ (OFFICE BMS)'],
                ['Trạng thái', 'Không thể kết xuất dữ liệu tại thời điểm này.'],
            ];
        }
    }
}
