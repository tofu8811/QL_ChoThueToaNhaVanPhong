<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Hệ Thống Quản Lý Và Cho Thuê Tòa Nhà Văn Phòng API",
    description: "Tài liệu RESTful API chuẩn hóa cho Đồ án Tốt nghiệp Quản lý và Cho thuê Tòa nhà Văn phòng.",
    contact: new OA\Contact(
        name: "Đội ngũ phát triển Backend",
        email: "contact@officeleasing.vn"
    )
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Máy chủ phát triển Localhost"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Nhập mã Bearer Token (Laravel Sanctum) để xác thực các API yêu cầu quyền hạn nội bộ."
)]
#[OA\Tag(name: "Provinces", description: "Danh mục Địa giới Hành chính (Tỉnh / Thành phố)")]
#[OA\Tag(name: "Buildings (Public)", description: "Cổng thông tin tra cứu Tòa nhà văn phòng chào thuê (Public Portal)")]
#[OA\Tag(name: "Buildings (Admin)", description: "Quản trị Tòa nhà, Biểu phí và Thư viện hình ảnh (Admin & BQL)")]
#[OA\Tag(name: "Units (Public)", description: "Cổng thông tin Mặt bằng văn phòng chào thuê khả dụng (Public Portal)")]
#[OA\Tag(name: "Units (Admin)", description: "Quản trị Mặt bằng sàn, sơ đồ Layout kỹ thuật và đổi trạng thái (Admin & BQL)")]

// ========================================================
// --- 1. CÁC SCHEMA VỎ BỌC DÙNG CHUNG (RESPONSE WRAPPERS) ---
// ========================================================
#[OA\Schema(
    schema: "ApiResponseError",
    title: "Phản hồi Lỗi Chuẩn",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(property: "message", type: "string", example: "Dữ liệu không hợp lệ hoặc không tìm thấy tài nguyên."),
        new OA\Property(property: "code", type: "integer", example: 400),
        new OA\Property(property: "errors", type: "object", nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: "ApiResponseValidationError",
    title: "Phản hồi Lỗi Kiểm Thực Dữ Liệu (422)",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(property: "message", type: "string", example: "Dữ liệu gửi lên không thỏa mãn quy tắc kiểm thực."),
        new OA\Property(property: "code", type: "integer", example: 422),
        new OA\Property(
            property: "errors",
            type: "object",
            example: ["field_name" => ["Trường dữ liệu này không hợp lệ hoặc bắt buộc."]]
        ),
    ]
)]
#[OA\Schema(
    schema: "PaginationMeta",
    title: "Thông tin Phân trang",
    properties: [
        new OA\Property(property: "current_page", type: "integer", example: 1),
        new OA\Property(property: "per_page", type: "integer", example: 15),
        new OA\Property(property: "total", type: "integer", example: 100),
        new OA\Property(property: "last_page", type: "integer", example: 7),
    ]
)]
#[OA\Schema(
    schema: "ApiResponseSuccess",
    title: "Phản hồi Thành công Chuẩn",
    properties: [
        new OA\Property(property: "status", type: "string", example: "success"),
        new OA\Property(property: "message", type: "string", example: "Thao tác thực hiện thành công."),
        new OA\Property(property: "code", type: "integer", example: 200),
        new OA\Property(property: "data", type: "object", nullable: true),
        new OA\Property(property: "errors", type: "object", nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: "ApiPaginateResponse",
    title: "Phản hồi Danh sách Phân trang",
    properties: [
        new OA\Property(property: "status", type: "string", example: "success"),
        new OA\Property(property: "message", type: "string", example: "Lấy danh sách thành công."),
        new OA\Property(property: "code", type: "integer", example: 200),
        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
        new OA\Property(property: "pagination", ref: "#/components/schemas/PaginationMeta"),
        new OA\Property(property: "errors", type: "object", nullable: true, example: null),
    ]
)]

// ========================================================
// --- 2. CÁC SCHEMA DỮ LIỆU ĐẦU VÀO FORM (REQUEST BODIES) ---
// ========================================================
#[OA\Schema(
    schema: "StoreBuildingInput",
    title: "Dữ liệu Tạo mới / Cập nhật Tòa nhà",
    required: ["building_code", "name", "address", "district", "province", "province_code", "total_floors", "total_leasable_area_sqm", "electricity_unit_price", "water_unit_price", "default_service_fee_per_sqm", "min_rent_price_per_sqm", "max_rent_price_per_sqm", "grade"],
    properties: [
        new OA\Property(property: "building_code", type: "string", example: "NEXUS-01"),
        new OA\Property(property: "name", type: "string", example: "Tòa Nhà The Nexus"),
        new OA\Property(property: "address", type: "string", example: "3A Tôn Đức Thắng"),
        new OA\Property(property: "ward", type: "string", example: "Bến Nghé"),
        new OA\Property(property: "district", type: "string", example: "Quận 1"),
        new OA\Property(property: "province", type: "string", example: "TP Hồ Chí Minh"),
        new OA\Property(property: "province_code", type: "integer", example: 79),
        new OA\Property(property: "total_floors", type: "integer", example: 35),
        new OA\Property(property: "total_basements", type: "integer", example: 5),
        new OA\Property(property: "total_leasable_area_sqm", type: "number", example: 55000),
        new OA\Property(property: "default_service_fee_per_sqm", type: "number", example: 150000),
        new OA\Property(property: "electricity_unit_price", type: "number", example: 4500),
        new OA\Property(property: "water_unit_price", type: "number", example: 25000),
        new OA\Property(property: "motorbike_parking_fee", type: "number", example: 350000),
        new OA\Property(property: "car_parking_fee", type: "number", example: 4500000),
        new OA\Property(property: "min_rent_price_per_sqm", type: "number", example: 850000),
        new OA\Property(property: "max_rent_price_per_sqm", type: "number", example: 1200000),
        new OA\Property(property: "grade", type: "string", example: "A", enum: ["A", "B", "C"]),
    ]
)]
#[OA\Schema(
    schema: "StoreUnitInput",
    title: "Dữ liệu Tạo mới / Cập nhật Mặt bằng",
    required: ["building_id", "unit_code", "floor_number", "area_sqm", "base_rental_price"],
    properties: [
        new OA\Property(property: "building_id", type: "string", format: "uuid", example: "0192e48e-xxxx-xxxx-xxxx-xxxxxxxxxxxx"),
        new OA\Property(property: "unit_code", type: "string", example: "NEX-0801"),
        new OA\Property(property: "name", type: "string", example: "Văn phòng Tầng 8 Phòng 01"),
        new OA\Property(property: "floor_number", type: "integer", example: 8),
        new OA\Property(property: "area_sqm", type: "number", example: 185.5),
        new OA\Property(property: "base_rental_price", type: "number", example: 950000),
        new OA\Property(property: "handover_condition", type: "string", example: "BASIC"),
        new OA\Property(property: "direction", type: "string", example: "Đông Nam"),
        new OA\Property(property: "status", type: "string", example: "AVAILABLE", enum: ["AVAILABLE", "EXPIRING_SOON", "LEASED", "MAINTENANCE"]),
    ]
)]
abstract class BaseDoc
{
}
