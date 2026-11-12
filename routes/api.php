<?php

use App\Http\Controllers\Api\V1\AdminUserApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BuildingApiController;
use App\Http\Controllers\Api\V1\CrmLeadApiController;
use App\Http\Controllers\Api\V1\LeadActivityApiController;
use App\Http\Controllers\Api\V1\PortalLeadApiController;
use App\Http\Controllers\Api\V1\ProvinceApiController;
use App\Http\Controllers\Api\V1\SaleUserApiController;
use App\Http\Controllers\Api\V1\TicketApiController;
use App\Http\Controllers\Api\V1\UnitApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Hệ Thống Quản Lý Và Cho Thuê Tòa Nhà Văn Phòng
|--------------------------------------------------------------------------
|
| Định tuyến RESTful API chuẩn hóa theo phiên bản (v1).
| Tự động áp dụng GlobalExceptionMiddleware và ForceJsonResponseMiddleware.
|
*/

Route::prefix('v1')->group(function () {

    // =========================================================================
    // MODULE 1: DANH MỤC BẤT ĐỘNG SẢN & PUBLIC PORTAL (CÔNG KHAI)
    // =========================================================================
    // 1. Địa giới hành chính
    Route::get('provinces', [ProvinceApiController::class, 'index'])->name('v1.provinces.index');
    Route::get('provinces/{code}', [ProvinceApiController::class, 'show'])->name('v1.provinces.show');

    // 2. Tra cứu Tòa nhà công khai (Public Portal)
    Route::get('portal/buildings', [BuildingApiController::class, 'portalIndex'])->name('v1.portal.buildings.index');
    Route::get('portal/buildings/{slug}', [BuildingApiController::class, 'portalShow'])->name('v1.portal.buildings.show');

    // 3. Tra cứu Mặt bằng công khai (Public Portal)
    Route::get('portal/units', [UnitApiController::class, 'portalIndex'])->name('v1.portal.units.index');
    Route::get('portal/units/{id}', [UnitApiController::class, 'portalShow'])->name('v1.portal.units.show');

    // =========================================================================
    // MODULE 1: QUẢN TRỊ TÒA NHÀ & MẶT BẰNG (ADMIN & BQL)
    // =========================================================================
    Route::prefix('admin')->group(function () {
        // Quản lý Tòa nhà & Thư viện hình ảnh
        Route::get('buildings', [BuildingApiController::class, 'adminIndex'])->name('v1.admin.buildings.index');
        Route::post('buildings', [BuildingApiController::class, 'store'])->name('v1.admin.buildings.store');
        Route::get('buildings/{id}', [BuildingApiController::class, 'show'])->name('v1.admin.buildings.show');
        Route::put('buildings/{id}', [BuildingApiController::class, 'update'])->name('v1.admin.buildings.update');
        Route::delete('buildings/{id}', [BuildingApiController::class, 'destroy'])->name('v1.admin.buildings.destroy');

        Route::post('buildings/{id}/images', [BuildingApiController::class, 'uploadImages'])->name('v1.admin.buildings.images.store');
        Route::delete('buildings/{id}/images/{imageId}', [BuildingApiController::class, 'deleteImage'])->name('v1.admin.buildings.images.destroy');
        Route::patch('buildings/{id}/images/{imageId}/set-primary', [BuildingApiController::class, 'setPrimaryImage'])->name('v1.admin.buildings.images.primary');

        // Quản lý Mặt bằng sàn & Layout
        Route::get('units', [UnitApiController::class, 'adminIndex'])->name('v1.admin.units.index');
        Route::post('units', [UnitApiController::class, 'store'])->name('v1.admin.units.store');
        Route::get('units/{id}', [UnitApiController::class, 'show'])->name('v1.admin.units.show');
        Route::put('units/{id}', [UnitApiController::class, 'update'])->name('v1.admin.units.update');
        Route::delete('units/{id}', [UnitApiController::class, 'destroy'])->name('v1.admin.units.destroy');

        Route::patch('units/{id}/status', [UnitApiController::class, 'updateStatus'])->name('v1.admin.units.status');
        Route::post('units/{id}/images', [UnitApiController::class, 'uploadImages'])->name('v1.admin.units.images.store');
        Route::delete('units/{id}/images/{imageId}', [UnitApiController::class, 'deleteImage'])->name('v1.admin.units.images.destroy');
    });

    // =========================================================================
    // MODULE 2: TIẾP NHẬN LEAD TỪ PORTAL (PUBLIC)
    // =========================================================================
    Route::post('portal/leads', [PortalLeadApiController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('v1.portal.leads.store');

    // =========================================================================
    // MODULE 2: CRM & QUẢN LÝ KHÁCH HÀNG TIỀM NĂNG (LEADS & ACTIVITIES)
    // =========================================================================
    Route::prefix('crm')->group(function () {
        // Quản lý Lead & Pipeline
        Route::get('leads', [CrmLeadApiController::class, 'index'])->name('v1.crm.leads.index');
        Route::post('leads', [CrmLeadApiController::class, 'store'])->name('v1.crm.leads.store');
        Route::get('leads/{id}', [CrmLeadApiController::class, 'show'])->name('v1.crm.leads.show');
        Route::put('leads/{id}', [CrmLeadApiController::class, 'update'])->name('v1.crm.leads.update');
        Route::delete('leads/{id}', [CrmLeadApiController::class, 'destroy'])->name('v1.crm.leads.destroy');

        // Hành vi chuyển trạng thái, điều phối & chuyển đổi
        Route::patch('leads/{id}/status', [CrmLeadApiController::class, 'updateStatus'])->name('v1.crm.leads.status');
        Route::post('leads/{id}/assign', [CrmLeadApiController::class, 'assign'])->name('v1.crm.leads.assign');
        Route::post('leads/{id}/convert-to-tenant', [CrmLeadApiController::class, 'convertToTenant'])->name('v1.crm.leads.convert');

        // Dòng thời gian chăm sóc & Nhật ký hoạt động
        Route::get('leads/{id}/activities', [LeadActivityApiController::class, 'index'])->name('v1.crm.leads.activities.index');
        Route::post('leads/{id}/activities', [LeadActivityApiController::class, 'store'])->name('v1.crm.leads.activities.store');
    });

    // =========================================================================
    // MODULE 5: XÁC THỰC (AUTH) & PHÂN QUYỀN TÒA NHÀ — ENDPOINTS 67 - 71
    // =========================================================================
    Route::prefix('auth')->group(function () {
        // 67. Đăng nhập Đa Tác nhân (Public)
        Route::post('login', [AuthController::class, 'login'])->name('v1.auth.login');

        // Các Endpoint yêu cầu Sanctum Bearer Token
        Route::middleware('auth:sanctum')->group(function () {
            // 68. Đăng xuất Thu hồi Token
            Route::post('logout', [AuthController::class, 'logout'])->name('v1.auth.logout');

            // 69. Xem Hồ sơ Cá nhân & Phạm vi Quản lý
            Route::get('me', [AuthController::class, 'me'])->name('v1.auth.me');

            // 70. Làm mới Phiên làm việc
            Route::post('refresh', [AuthController::class, 'refresh'])->name('v1.auth.refresh');

            // 71. Đổi Mật khẩu Cá nhân
            Route::put('password', [AuthController::class, 'changePassword'])->name('v1.auth.password');
        });
    });

    // =========================================================================
    // MODULE 4: QUẢN LÝ YÊU CẦU HỖ TRỢ & SỰ CỐ BẢO TRÌ (TICKETS & MAINTENANCE)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {
        // Cổng Khách thuê (Tenant Portal)
        Route::prefix('tenant')->group(function () {
            // 58. Gửi Yêu cầu Hỗ trợ Mới
            Route::post('tickets', [TicketApiController::class, 'createTenantTicket'])->name('v1.tenant.tickets.store');
            // 65. Khách thuê Xác nhận Nghiệm thu
            Route::post('tickets/{id}/confirm', [TicketApiController::class, 'confirm'])->name('v1.tenant.tickets.confirm');
            // 66. Khách thuê Đánh giá Chất lượng Phục vụ (CSAT)
            Route::post('tickets/{id}/rate', [TicketApiController::class, 'rate'])->name('v1.tenant.tickets.rate');
        });

        // Xử lý Sự cố & Điều phối Kỹ thuật
        Route::prefix('tickets')->group(function () {
            // 59. Chi tiết Tiến trình Ticket & Lịch sử
            Route::get('{id}', [TicketApiController::class, 'show'])->name('v1.tickets.show');
            // 60. Cập nhật Nội dung Ticket (khi còn OPEN)
            Route::put('{id}', [TicketApiController::class, 'update'])->name('v1.tickets.update');
            // 61. Xóa mềm Ticket (Admin)
            Route::delete('{id}', [TicketApiController::class, 'destroy'])->name('v1.tickets.destroy');
            // 62. Tiếp nhận & Phân công Xử lý (PM / Admin)
            Route::patch('{id}/assign', [TicketApiController::class, 'assign'])->name('v1.tickets.assign');
            // 63. Cập nhật Tiến độ Sửa chữa (ON_HOLD / RESOLVED)
            Route::patch('{id}/status', [TicketApiController::class, 'updateStatus'])->name('v1.tickets.status');
            // 64. Tải lên Ảnh Bằng chứng (Before / After)
            Route::post('{id}/proof-images', [TicketApiController::class, 'uploadProofImages'])->name('v1.tickets.proof_images');
        });
    });

    // =========================================================================
    // MODULE 5: QUẢN TRỊ NHÂN SỰ & QUẢN TRỊ VIÊN (ADMINS)
    // =========================================================================
    Route::middleware(['auth:sanctum', 'super_admin'])->prefix('admin/users/admins')->group(function () {
        // 72. Danh sách Quản trị viên
        Route::get('', [AdminUserApiController::class, 'index'])->name('v1.admin.users.admins.index');
        // 73. Tạo mới Quản trị viên
        Route::post('', [AdminUserApiController::class, 'store'])->name('v1.admin.users.admins.store');
        // 74. Chi tiết Quản trị viên
        Route::get('{id}', [AdminUserApiController::class, 'show'])->name('v1.admin.users.admins.show');
        // 75. Cập nhật Quản trị viên
        Route::put('{id}', [AdminUserApiController::class, 'update'])->name('v1.admin.users.admins.update');
        // 76. Xóa mềm Quản trị viên
        Route::delete('{id}', [AdminUserApiController::class, 'destroy'])->name('v1.admin.users.admins.destroy');
    });

    // =========================================================================
    // MODULE 5: NHÂN SỰ & QUẢN TRỊ SALES
    // =========================================================================
    Route::middleware('auth:sanctum')->prefix('admin/users/sales')->group(function () {
        // 77. Danh sách Nhân viên Sales
        Route::get('', [SaleUserApiController::class, 'index'])->name('v1.admin.users.sales.index');
        // 78. Tạo mới Nhân viên Sales
        Route::post('', [SaleUserApiController::class, 'store'])->name('v1.admin.users.sales.store');
        // 79. Chi tiết Nhân viên Sales
        Route::get('{id}', [SaleUserApiController::class, 'show'])->name('v1.admin.users.sales.show');
        // 80. Cập nhật Nhân viên Sales
        Route::put('{id}', [SaleUserApiController::class, 'update'])->name('v1.admin.users.sales.update');
        // 81. Xóa mềm Nhân viên Sales
        Route::delete('{id}', [SaleUserApiController::class, 'destroy'])->name('v1.admin.users.sales.destroy');
    });

});

// Nhóm Endpoint kiểm thử hệ thống bắt lỗi tập trung (Global Exception Handling)
if (app()->environment('local', 'testing')) {
    Route::prefix('test-errors')->group(function () {
        Route::get('model-not-found', function () {
            $exception = new \Illuminate\Database\Eloquent\ModelNotFoundException();
            $exception->setModel(\App\Models\Unit::class, ['fake-id-123']);
            throw $exception;
        });

        Route::post('validation-error', function (\Illuminate\Http\Request $request) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'building_id' => 'required',
                'price_per_m2' => 'required|numeric|min:1',
            ], [
                'building_id.required' => 'Mã tòa nhà là bắt buộc.',
                'price_per_m2.required' => 'Đơn giá thuê là bắt buộc.',
                'price_per_m2.min' => 'Đơn giá thuê phải lớn hơn 0.',
            ]);

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }

            return response()->json(['message' => 'Hợp lệ']);
        });

        Route::get('business-error', function () {
            throw new \App\Exceptions\BusinessException('Mặt bằng này đã có hợp đồng thuê hiệu lực trong khoảng thời gian đã chọn.', 422);
        });

        Route::get('db-error', function () {
            $pdoException = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry', 23000);
            $pdoException->errorInfo = ['23000', 1062, "Duplicate entry 'VP-301' for key 'units_unit_code_unique'"];

            throw new \Illuminate\Database\QueryException('mysql', 'insert into units ...', [], $pdoException);
        });

        Route::get('auth-error', function () {
            throw new \Illuminate\Auth\AuthenticationException('Chưa đăng nhập hoặc phiên làm việc đã hết hạn.');
        });

        Route::get('success-sample', function () {
            $controller = new class {
                use \App\Traits\ApiResponse;
            };

            return $controller->successResponse([
                'unit_code' => 'VP-301',
                'area' => 125.5,
                'status' => 'available',
            ], 'Lấy thông tin mặt bằng thành công.');
        });
    });
}
