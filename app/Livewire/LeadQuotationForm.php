<?php

namespace App\Livewire;

use App\Models\Building;
use App\Models\Province;
use App\Models\Unit;
use App\Services\LeadService;
use Livewire\Component;

/**
 * Livewire Component: Form Đăng Ký Nhận Báo Giá & Đặt Lịch Khảo Sát (Module 2).
 * Tích hợp gọi trực tiếp LeadService (chống trùng lặp 30 ngày, phân bổ Sales Least-Active-Leads).
 */
class LeadQuotationForm extends Component
{
    public string $contact_full_name = '';
    public string $phone = '';
    public string $email = '';
    public string $company_name = '';
    public ?int $province_code = null;
    public ?string $building_id = null;
    public ?string $unit_id = null;
    public ?float $desired_area_sqm = null;
    public ?float $budget_min = null;
    public ?float $budget_max = null;
    public ?string $preferred_site_tour_at = null;
    public string $customer_note = '';

    public bool $isSubmitted = false;
    public string $resultTitle = '';
    public string $resultMessage = '';
    public string $resultType = 'success'; // 'success' hoặc 'info'

    /**
     * Khởi tạo form với tùy chọn chọn trước Tòa nhà và Mặt bằng.
     */
    public function mount(?string $buildingId = null, ?string $unitId = null): void
    {
        if ($buildingId) {
            $this->building_id = $buildingId;
            $building = Building::find($buildingId);
            if ($building && $building->province_code) {
                $this->province_code = (int) $building->province_code;
            }
        }

        if ($unitId) {
            $this->unit_id = $unitId;
        }
    }

    /**
     * Khi thay đổi khu vực (tỉnh/thành phố), reset lựa chọn tòa nhà và mặt bằng tương ứng.
     */
    public function updatedProvinceCode(): void
    {
        $this->building_id = null;
        $this->unit_id = null;
    }

    /**
     * Khi thay đổi tòa nhà, reset lựa chọn mặt bằng tương ứng.
     */
    public function updatedBuildingId(): void
    {
        $this->unit_id = null;
    }

    /**
     * Khi chọn mặt bằng, tự động điền diện tích thực tế của mặt bằng vào ô diện tích cần tìm.
     */
    public function updatedUnitId(?string $unitId): void
    {
        if ($unitId) {
            $unit = Unit::find($unitId);
            if ($unit && $unit->area_sqm) {
                $this->desired_area_sqm = (float) $unit->area_sqm;
            }
        }
    }

    /**
     * Tự động loại bỏ mọi ký tự không phải số khi thuộc tính phone thay đổi.
     */
    public function updatedPhone($value): void
    {
        $this->phone = preg_replace('/[^0-9]/', '', (string) $value);
    }

    /**
     * Định nghĩa các quy tắc kiểm tra dữ liệu đầu vào.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'contact_full_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9,10}$/'],
            'email' => ['required', 'string', 'email:rfc,filter', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'province_code' => ['nullable', 'integer', 'exists:provinces,code'],
            'building_id' => ['required', 'string', 'exists:buildings,id'],
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'desired_area_sqm' => ['nullable', 'numeric', 'min:1', 'max:100000'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'preferred_site_tour_at' => [
                'nullable',
                'date',
                'after_or_equal:' . now()->addDays(3)->format('Y-m-d H:i:s'),
            ],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'contact_full_name.required' => 'Vui lòng nhập họ và tên người liên hệ.',
            'contact_full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'phone.required' => 'Vui lòng cung cấp số điện thoại hợp lệ.',
            'phone.regex' => 'Vui lòng cung cấp số điện thoại hợp lệ.',
            'email.required' => 'Vui lòng cung cấp địa chỉ email hợp lệ.',
            'email.email' => 'Vui lòng cung cấp địa chỉ email hợp lệ.',
            'building_id.required' => 'Vui lòng chọn tòa nhà quan tâm.',
            'building_id.exists' => 'Tòa nhà được chọn không tồn tại trong hệ thống.',
            'unit_id.exists' => 'Mặt bằng đã chọn không tồn tại.',
            'desired_area_sqm.numeric' => 'Diện tích cần tìm phải là dạng số.',
            'desired_area_sqm.min' => 'Diện tích cần tìm phải lớn hơn 0 m².',
            'preferred_site_tour_at.after_or_equal' => 'Thời gian muốn khảo sát thực tế phải cách thời điểm gửi yêu cầu ít nhất 3 ngày (từ ngày ' . now()->addDays(3)->format('d/m/Y H:i') . ' trở đi).',
        ];
    }

    /**
     * Tiếp nhận và xử lý yêu cầu báo giá/khảo sát.
     */
    public function submit(LeadService $leadService): void
    {
        // Chuẩn hóa SĐT chỉ nhận chữ số trước khi validate
        $this->phone = preg_replace('/[^0-9]/', '', (string) $this->phone);

        $validated = $this->validate();

        $payload = [
            'contact_full_name' => $validated['contact_full_name'],
            'company_name' => $validated['company_name'] ?: null,
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'lead_source' => 'WEBSITE',
            'building_id' => $validated['building_id'] ?: null,
            'unit_id' => !empty($validated['building_id']) ? ($validated['unit_id'] ?: null) : null,
            'desired_area_sqm' => $validated['desired_area_sqm'] ? (float) $validated['desired_area_sqm'] : null,
            'budget_min' => $validated['budget_min'] ? (float) $validated['budget_min'] : null,
            'budget_max' => $validated['budget_max'] ? (float) $validated['budget_max'] : null,
            'preferred_site_tour_at' => $validated['preferred_site_tour_at'] ?: null,
            'customer_note' => $validated['customer_note'] ?: null,
        ];

        $lead = $leadService->storePortalLead($payload);

        // Kiểm tra xem đây là Lead mới tạo hay nối tiếp vào hồ sơ cũ 30 ngày
        if ($lead->wasRecentlyCreated) {
            $this->resultType = 'success';
            $this->resultTitle = 'Tiếp Nhận Báo Giá Thành Công!';
            $targetDesc = $lead->building ? "tòa nhà {$lead->building->name}" : "khu vực văn phòng bạn quan tâm";
            $this->resultMessage = "Cảm ơn quý khách {$lead->contact_full_name}. Yêu cầu tư vấn {$targetDesc} đã được gửi tới chuyên viên phụ trách. Chúng tôi sẽ phản hồi trong vòng 30 phút.";
        } else {
            $this->resultType = 'info';
            $this->resultTitle = 'Hồ Sơ Của Quý Khách Đã Được Cập Nhật!';
            $this->resultMessage = "Hệ thống ghi nhận quý khách đã có liên hệ tư vấn trong 30 ngày qua. Yêu cầu mới này đã được nối trực tiếp vào hồ sơ chăm sóc của chuyên viên phụ trách.";
        }

        $this->isSubmitted = true;
        $this->dispatch('mo-toast', message: $this->resultMessage, type: $this->resultType, title: $this->resultTitle);
    }

    /**
     * Làm mới form để gửi yêu cầu khác.
     */
    public function resetForm(): void
    {
        $this->reset([
            'contact_full_name',
            'phone',
            'email',
            'company_name',
            'province_code',
            'building_id',
            'unit_id',
            'desired_area_sqm',
            'budget_min',
            'budget_max',
            'preferred_site_tour_at',
            'customer_note',
            'isSubmitted',
            'resultTitle',
            'resultMessage',
        ]);
    }

    public function render()
    {
        // 100% dữ liệu động từ Database theo quy chuẩn DB-driven
        $provinces = Province::query()
            ->withCount(['buildings' => fn($q) => $q->where('is_published', true)])
            ->orderBy('name')
            ->get(['code', 'name']);

        $buildings = Building::query()
            ->published()
            ->when($this->province_code, function ($q) {
                $q->where('province_code', $this->province_code);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'district', 'grade', 'min_rent_price_per_sqm', 'province_code']);

        $units = collect();
        if ($this->building_id) {
            $units = Unit::query()
                ->where('building_id', $this->building_id)
                ->whereIn('status', ['AVAILABLE', 'EXPIRING_SOON'])
                ->orderBy('floor_number')
                ->get(['id', 'floor_number', 'unit_code', 'area_sqm', 'base_rental_price']);
        }

        return view('livewire.lead-quotation-form', [
            'provinces' => $provinces,
            'buildings' => $buildings,
            'units' => $units,
        ]);
    }
}
