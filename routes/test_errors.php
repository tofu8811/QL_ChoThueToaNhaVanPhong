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
| Test Routes - Kiểm thử Hệ Thống Xử Lý Lỗi Tập Trung (Global Exception)
|--------------------------------------------------------------------------
|
| Định tuyến phục vụ kiểm thử tự động (PHPUnit Feature Tests) và Postman.
| File này chỉ được nạp ở môi trường 'local' hoặc 'testing'.
|
*/

Route::prefix('test-errors')->group(function () {
    Route::get('model-not-found', function () {
        $exception = new ModelNotFoundException();
        $exception->setModel(Unit::class, ['fake-id-123']);
        throw $exception;
    });

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

    Route::get('business-error', function () {
        throw new BusinessException('Mặt bằng này đã có hợp đồng thuê hiệu lực trong khoảng thời gian đã chọn.', 422);
    });

    Route::get('db-error', function () {
        $pdoException = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry', 23000);
        $pdoException->errorInfo = ['23000', 1062, "Duplicate entry 'VP-301' for key 'units_unit_code_unique'"];

        throw new QueryException('mysql', 'insert into units ...', [], $pdoException);
    });

    Route::get('auth-error', function () {
        throw new AuthenticationException('Chưa đăng nhập hoặc phiên làm việc đã hết hạn.');
    });

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
