<?php

use App\Exceptions\BusinessException;
use App\Models\Unit;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Định tuyến cho toàn bộ hệ thống RESTful API B2B của dự án Quản lý Tòa nhà.
| Tự động áp dụng nhóm middleware 'api' với ForceJsonResponseMiddleware
| và GlobalExceptionMiddleware.
|
*/

// Nhóm Endpoint kiểm thử hệ thống bắt lỗi tập trung (Global Exception Handling)
Route::prefix('test-errors')->group(function () {
    // 1. Kiểm thử ModelNotFoundException (Mặt bằng không tồn tại) -> Kỳ vọng 404
    Route::get('model-not-found', function () {
        $exception = new ModelNotFoundException();
        $exception->setModel(Unit::class, ['fake-id-123']);
        throw $exception;
    });

    // 2. Kiểm thử ValidationException -> Kỳ vọng 422
    Route::post('validation-error', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'building_id' => 'required',
            'price_per_m2' => 'required|numeric|min:1',
        ], [
            'building_id.required' => 'Mã tòa nhà là bắt buộc.',
            'price_per_m2.required' => 'Đơn giá thuê là bắt buộc.',
            'price_per_m2.min' => 'Đơn giá thuê phải lớn hơn 0.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return response()->json(['message' => 'Hợp lệ']);
    });

    // 3. Kiểm thử BusinessException từ Service Layer -> Kỳ vọng 422 hoặc mã tùy chọn
    Route::get('business-error', function () {
        throw new BusinessException('Mặt bằng này đã có hợp đồng thuê hiệu lực trong khoảng thời gian đã chọn.', 422);
    });

    // 4. Kiểm thử QueryException (Lỗi trùng lặp CSDL MySQL 1062) -> Kỳ vọng 409
    Route::get('db-error', function () {
        $pdoException = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry', 23000);
        $pdoException->errorInfo = ['23000', 1062, "Duplicate entry 'VP-301' for key 'units_unit_code_unique'"];

        throw new QueryException('mysql', 'insert into units ...', [], $pdoException);
    });

    // 5. Kiểm thử AuthenticationException -> Kỳ vọng 401
    Route::get('auth-error', function () {
        throw new AuthenticationException('Chưa đăng nhập hoặc phiên làm việc đã hết hạn.');
    });

    // 6. Kiểm thử phản hồi thành công chuẩn qua Trait ApiResponse
    Route::get('success-sample', function () {
        $controller = new class {
            use ApiResponse;
        };

        return $controller->successResponse([
            'unit_code' => 'VP-301',
            'area' => 125.5,
            'status' => 'available',
        ], 'Lấy thông tin mặt bằng thành công.');
    });
});
