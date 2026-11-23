<?php

namespace App\Http\Requests\V1\Building;

use App\Http\Requests\V1\BaseApiRequest;

class StoreBuildingRequest extends BaseApiRequest
{
    /**
     * Chuẩn hóa và bù đắp dữ liệu đầu vào trước khi xác thực (Input Normalization).
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        // 1. Ánh xạ diện tích khả dụng nếu frontend gửi total_net_area
        if ($this->missing('total_leasable_area_sqm') && $this->has('total_net_area')) {
            $merge['total_leasable_area_sqm'] = $this->input('total_net_area');
        }

        // 2. Ánh xạ số tầng hầm nếu frontend gửi basements
        if ($this->missing('total_basements') && $this->has('basements')) {
            $merge['total_basements'] = (int) $this->input('basements');
        }

        // 3. Tự suy diễn tên tỉnh/thành phố từ province_code nếu chưa có
        if ($this->missing('province') && $this->filled('province_code')) {
            $province = \App\Models\Province::find($this->input('province_code'));
            if ($province) {
                $merge['province'] = $province->name;
            }
        }

        // 4. Bù đắp phường/xã nếu frontend chỉ gửi quận/huyện hoặc để trống
        if (($this->missing('ward') || empty($this->input('ward'))) && $this->filled('district')) {
            $district = $this->input('district');
            $merge['ward'] = "Phường trung tâm {$district}";
        }

        // 5. Chuẩn hóa đơn giá thuê nếu truyền min mà thiếu max
        if ($this->missing('max_rent_price_per_sqm') && $this->filled('min_rent_price_per_sqm')) {
            $minPrice = (float) $this->input('min_rent_price_per_sqm');
            $merge['max_rent_price_per_sqm'] = round($minPrice * 1.25);
        }

        // 6. Ánh xạ phí gửi xe máy nếu frontend gửi motorcycle_parking_fee
        if ($this->missing('motorbike_parking_fee') && $this->has('motorcycle_parking_fee')) {
            $merge['motorbike_parking_fee'] = (float) $this->input('motorcycle_parking_fee');
        }

        // 7. Bù đắp đơn giá điện & nước nếu frontend chỉ gửi chuỗi mô tả hoặc thiếu
        if ($this->missing('electricity_unit_price') && $this->has('electricity_fee_desc')) {
            $merge['electricity_unit_price'] = 4500;
        }
        if ($this->missing('water_unit_price') && $this->has('water_fee_desc')) {
            $merge['water_unit_price'] = 25000;
        }

        // 8. Bù đắp giá trị mặc định cho thông số kỹ thuật & chính sách thuê nếu thiếu
        if ($this->missing('ceiling_height_meters') || empty($this->input('ceiling_height_meters'))) {
            $merge['ceiling_height_meters'] = 2.70;
        }
        if ($this->missing('elevators_count') || empty($this->input('elevators_count'))) {
            $merge['elevators_count'] = 1;
        }
        if ($this->missing('deposit_term_months') || empty($this->input('deposit_term_months'))) {
            $merge['deposit_term_months'] = 3;
        }
        if ($this->missing('payment_term_months') || empty($this->input('payment_term_months'))) {
            $merge['payment_term_months'] = 3;
        }
        if ($this->missing('overtime_fee_per_hour') || empty($this->input('overtime_fee_per_hour'))) {
            $merge['overtime_fee_per_hour'] = 0.00;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Quy tắc xác thực dữ liệu đầu vào khi tạo mới Tòa nhà văn phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_code' => ['nullable', 'string', 'max:50', 'unique:buildings,building_code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'province_code' => ['required', 'integer', 'exists:provinces,code'],
            'district_code' => ['nullable', 'integer'],
            'ward_code' => ['nullable', 'integer'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'total_floors' => ['required', 'integer', 'min:1'],
            'total_basements' => ['nullable', 'integer', 'min:0'],
            'total_leasable_area_sqm' => ['required', 'numeric', 'min:1'],
            'ceiling_height_meters' => ['nullable', 'numeric', 'min:1'],
            'elevators_count' => ['nullable', 'integer', 'min:0'],
            'air_conditioner_spec' => ['nullable', 'string', 'max:255'],
            'backup_generator_spec' => ['nullable', 'string', 'max:255'],
            'working_hours_desc' => ['nullable', 'string', 'max:255'],
            'overtime_fee_per_hour' => ['nullable', 'numeric', 'min:0'],
            'motorbike_parking_fee' => ['nullable', 'numeric', 'min:0'],
            'car_parking_fee' => ['nullable', 'numeric', 'min:0'],
            'electricity_unit_price' => ['required', 'numeric', 'min:0'],
            'water_unit_price' => ['required', 'numeric', 'min:0'],
            'default_service_fee_per_sqm' => ['required', 'numeric', 'min:0'],
            'min_rent_price_per_sqm' => ['nullable', 'numeric', 'min:0'],
            'max_rent_price_per_sqm' => ['nullable', 'numeric', 'gte:min_rent_price_per_sqm'],
            'deposit_term_months' => ['nullable', 'integer', 'min:1'],
            'payment_term_months' => ['nullable', 'integer', 'min:1'],
            'grade' => ['required', 'string', 'in:A,B,C,a,b,c'],
            'direction' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_published' => ['nullable', 'boolean'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'max:5120'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tùy biến.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'building_code.unique' => 'Mã định danh tòa nhà đã tồn tại trên hệ thống.',
            'province_code.exists' => 'Mã tỉnh/thành phố không tồn tại trong danh mục.',
            'total_floors.min' => 'Tổng số tầng nổi phải từ 1 trở lên.',
            'max_rent_price_per_sqm.gte' => 'Giá thuê tối đa phải lớn hơn hoặc bằng giá thuê tối thiểu.',
            'grade.in' => 'Phân hạng văn phòng chỉ chấp nhận hạng A, B hoặc C.',
            'images.*.image' => 'Tệp tải lên phải là định dạng hình ảnh hợp lệ (JPG, PNG, WEBP).',
            'images.*.max' => 'Dung lượng mỗi ảnh tải lên tối đa là 5MB.',
        ];
    }
}
