<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Lớp FormRequest cơ sở cho toàn bộ API phân hệ V1.
 * Thiết lập mặc định authorize = true và cung cấp các hàm tiện ích dùng chung.
 */
abstract class BaseApiRequest extends FormRequest
{
    /**
     * Mặc định cho phép thực thi, kiểm soát quyền hạn được giao cho Middleware / Policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Kiểm tra xem request hiện tại có phải là tác vụ cập nhật (PUT hoặc PATCH) hay không.
     */
    public function isUpdateRequest(): bool
    {
        return in_array($this->method(), ['PUT', 'PATCH'], true);
    }

    /**
     * Tiện ích trích xuất ID định danh từ Route parameter.
     */
    public function getResourceId(?string $paramName = null): ?string
    {
        if ($paramName && $this->route($paramName)) {
            $param = $this->route($paramName);
            return is_object($param) ? ($param->id ?? null) : (string) $param;
        }

        foreach (['id', 'building', 'unit', 'contract', 'tenant', 'ticket', 'lead', 'sale', 'accountant', 'property_manager', 'admin'] as $name) {
            if ($this->route($name)) {
                $param = $this->route($name);
                return is_object($param) ? ($param->id ?? null) : (string) $param;
            }
        }

        return null;
    }
}
