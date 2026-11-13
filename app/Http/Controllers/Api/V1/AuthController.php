<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthController
 * 
 * Skinny Controller điều phối các thao tác xác thực đa tác nhân (Module 5: Endpoints 67 - 71).
 * Kế thừa BaseApiController để chuẩn hóa 100% định dạng phản hồi ApiResponse của dự án.
 */
class AuthController extends BaseApiController
{
    /**
     * Khởi tạo Controller với AuthService được inject tự động.
     *
     * @param  AuthService  $authService
     */
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Endpoint 67: Đăng nhập Đa Tác nhân (Multi-guard Login).
     * POST /api/v1/auth/login
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->only('email', 'password'),
                $request->input('role')
            );

            return $this->successResponse($result, 'Đăng nhập thành công.', Response::HTTP_OK);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Lỗi không xác định khi đăng nhập: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Đã có lỗi xảy ra trên hệ thống. Vui lòng thử lại sau.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint 68: Đăng xuất Thu hồi Token (Revoke Current Token).
     * POST /api/v1/auth/logout
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return $this->successResponse(null, 'Đăng xuất tài khoản và thu hồi phiên làm việc thành công.');
        } catch (\Throwable $e) {
            Log::error('Lỗi khi đăng xuất: ' . $e->getMessage());

            return $this->errorResponse('Không thể hoàn tất đăng xuất.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint 69: Xem Hồ sơ Cá nhân & Phạm vi Quản lý (Profile & Building Scope).
     * GET /api/v1/auth/me
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $profile = $this->authService->getProfile($request->user());

            return $this->successResponse($profile, 'Lấy thông tin hồ sơ người dùng thành công.');
        } catch (\Throwable $e) {
            Log::error('Lỗi khi truy xuất thông tin cá nhân: ' . $e->getMessage());

            return $this->errorResponse('Không thể lấy thông tin hồ sơ cá nhân.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint 70: Làm mới Phiên làm việc (Refresh Token).
     * POST /api/v1/auth/refresh
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refreshToken($request->user());

            return $this->successResponse($result, 'Làm mới phiên làm việc thành công.');
        } catch (\Throwable $e) {
            Log::error('Lỗi khi làm mới token: ' . $e->getMessage());

            return $this->errorResponse('Không thể làm mới phiên làm việc.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint 71: Đổi Mật khẩu Cá nhân (Change Password).
     * PUT /api/v1/auth/password
     *
     * @param  ChangePasswordRequest  $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword(
                $request->user(),
                $request->input('current_password'),
                $request->input('password')
            );

            return $this->successResponse(null, 'Đổi mật khẩu tài khoản thành công.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Lỗi khi đổi mật khẩu: ' . $e->getMessage());

            return $this->errorResponse('Không thể cập nhật mật khẩu mới.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
