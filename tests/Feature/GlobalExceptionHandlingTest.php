<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Class GlobalExceptionHandlingTest
 *
 * Kiểm thử tự động tính toàn vẹn của Bộ Xử Lý Lỗi Tập Trung (Global Exception Handling)
 * và Tầng Middleware theo đúng tiêu chuẩn Tuần 05:
 * Mẫu JSON chuẩn: { "status": "error", "message": "...", "code": 404, "errors": ... }
 */
class GlobalExceptionHandlingTest extends TestCase
{
    /**
     * Test 1: Bắt ModelNotFoundException (Mặt bằng không tồn tại) trả về đúng mã 404 và JSON chuẩn.
     */
    public function test_model_not_found_returns_formatted_404_json(): void
    {
        $response = $this->getJson('/api/test-errors/model-not-found');

        $response->assertStatus(404)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Mặt bằng không tồn tại.',
                'code'    => 404,
                'errors'  => null,
            ]);
    }

    /**
     * Test 2: Bắt lỗi đường dẫn không tồn tại (Router 404) trả về JSON chuẩn, không trả HTML.
     */
    public function test_route_not_found_returns_formatted_404_json(): void
    {
        $response = $this->get('/api/endpoint-hoan-toan-khong-ton-tai-xyz');

        $response->assertStatus(404)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Đường dẫn API không tồn tại.',
                'code'    => 404,
            ]);
    }

    /**
     * Test 3: Bắt ValidationException trả về mã 422 kèm danh sách errors chi tiết.
     */
    public function test_validation_exception_returns_formatted_422_with_errors(): void
    {
        $response = $this->postJson('/api/test-errors/validation-error', []);

        $response->assertStatus(422)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Dữ liệu gửi lên không hợp lệ.',
                'code'    => 422,
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'errors' => [
                    'building_id',
                    'price_per_m2',
                ],
            ]);
    }

    /**
     * Test 4: Bắt BusinessException từ Service Layer trả về đúng mã và thông báo tùy biến.
     */
    public function test_business_exception_returns_custom_code_and_message(): void
    {
        $response = $this->getJson('/api/test-errors/business-error');

        $response->assertStatus(422)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Mặt bằng này đã có hợp đồng thuê hiệu lực trong khoảng thời gian đã chọn.',
                'code'    => 422,
                'errors'  => null,
            ]);
    }

    /**
     * Test 5: Bắt QueryException (Lỗi CSDL MySQL 1062) được DatabaseErrorClassifier phân loại trả về 409 Conflict.
     */
    public function test_database_query_exception_is_classified_properly(): void
    {
        $response = $this->getJson('/api/test-errors/db-error');

        $response->assertStatus(409)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Dữ liệu đã tồn tại trong hệ thống (Trùng lặp dữ liệu duy nhất).',
                'code'    => 409,
                'errors'  => null,
            ]);
    }

    /**
     * Test 6: Bắt AuthenticationException trả về mã 401 JSON, không redirect về login HTML.
     */
    public function test_authentication_exception_returns_401_json(): void
    {
        // Gửi request thông thường KHÔNG có header Accept: application/json
        // ForceJsonResponseMiddleware vẫn phải ép nhận JSON
        $response = $this->get('/api/test-errors/auth-error');

        $response->assertStatus(401)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Chưa xác thực hoặc phiên làm việc đã hết hạn.',
                'code'    => 401,
            ]);
    }

    /**
     * Test 7: Phản hồi thành công từ ApiResponse Trait khớp đúng cấu trúc chuẩn.
     */
    public function test_success_response_matches_standard_format(): void
    {
        $response = $this->getJson('/api/test-errors/success-sample');

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Lấy thông tin mặt bằng thành công.',
                'code'    => 200,
                'data'    => [
                    'unit_code' => 'VP-301',
                    'area'      => 125.5,
                    'status'    => 'available',
                ],
            ]);
    }
}
