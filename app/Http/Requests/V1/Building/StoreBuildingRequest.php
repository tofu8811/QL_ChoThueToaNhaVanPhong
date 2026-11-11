<?php

namespace App\Http\Requests\V1\Building;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuildingRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền thực hiện request này hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực dữ liệu đầu vào khi tạo mới Tòa nhà văn phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_code' => ['required', 'string', 'max:50', 'unique:buildings,building_code'],
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
            'min_rent_price_per_sqm' => ['required', 'numeric', 'min:0'],
            'max_rent_price_per_sqm' => ['required', 'numeric', 'gte:min_rent_price_per_sqm'],
            'deposit_term_months' => ['nullable', 'integer', 'min:1'],
            'payment_term_months' => ['nullable', 'integer', 'min:1'],
            'grade' => ['required', 'string', 'in:A,B,C,a,b,c'],
            'direction' => ['nullable', 'string', 'max:50'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt thân thiện.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'building_code.required' => 'Mã định danh tòa nhà là bắt buộc.',
            'building_code.unique' => 'Mã định danh tòa nhà đã tồn tại trên hệ thống.',
            'name.required' => 'Tên tòa nhà văn phòng là bắt buộc.',
            'address.required' => 'Địa chỉ tòa nhà là bắt buộc.',
            'district.required' => 'Quận / Huyện là bắt buộc.',
            'province.required' => 'Tỉnh / Thành phố là bắt buộc.',
            'province_code.required' => 'Mã tỉnh/thành phố hành chính là bắt buộc.',
            'province_code.exists' => 'Mã tỉnh/thành phố không tồn tại trong danh mục.',
            'total_floors.required' => 'Tổng số tầng nổi là bắt buộc và phải từ 1 trở lên.',
            'total_leasable_area_sqm.required' => 'Tổng diện tích sàn cho thuê là bắt buộc.',
            'electricity_unit_price.required' => 'Đơn giá điện là bắt buộc.',
            'water_unit_price.required' => 'Đơn giá nước là bắt buộc.',
            'default_service_fee_per_sqm.required' => 'Phí quản lý dịch vụ mặc định là bắt buộc.',
            'min_rent_price_per_sqm.required' => 'Giá thuê sàn tối thiểu là bắt buộc.',
            'max_rent_price_per_sqm.required' => 'Giá thuê sàn tối đa là bắt buộc.',
            'max_rent_price_per_sqm.gte' => 'Giá thuê tối đa phải lớn hơn hoặc bằng giá thuê tối thiểu.',
            'grade.required' => 'Phân hạng văn phòng (A, B, C) là bắt buộc.',
            'grade.in' => 'Phân hạng văn phòng chỉ chấp nhận hạng A, B hoặc C.',
        ];
    }
}
