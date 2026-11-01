# Hệ Thống Quản Lý & Cho Thuê Tòa Nhà Văn Phòng (Office Leasing Management System)

> **Tech Stack:** Laravel 11, TALL Stack (Tailwind CSS, Alpine.js, Laravel Reverb, Livewire 3), Filament v3, MySQL 8.0, Supabase Storage.  
> **Kiến trúc:** Layered Architecture (N-Tier) kết hợp Modular Service Pattern.

---

## 🛠 Hướng Dẫn Cài Đặt Dành Cho Thành Viên Nhóm

Khi clone repository về máy cá nhân, thực hiện theo các bước sau:

```bash
# 1. Cài đặt dependencies PHP & Node.js
composer install
npm install

# 2. Tạo file cấu hình môi trường .env
cp .env.example .env

# 3. Tạo Application Key
php artisan key:generate

# 4. Cấu hình cơ sở dữ liệu trong file .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 5. Chạy migrate và tạo dữ liệu mẫu
php artisan migrate --seed

# 6. Build assets giao diện
npm run build

# 7. Khởi động server
php artisan serve
```

## 🌿 Quy Chuẩn Git Flow Của Nhóm
- `main`: Nhánh chỉ chứa bản phát hành ổn định (Bị khóa, cấm push trực tiếp).
- `develop`: Nhánh làm việc trung tâm để ghép code tính năng.
- `feature/[tên-tính-năng]`: Tách từ `develop` để làm việc độc lập (Ví dụ: `feature/contract-management`).
- **Commit Message chuẩn Conventional Commits**:
  - `feat: ...` (thêm tính năng mới)
  - `fix: ...` (sửa lỗi)
  - `docs: ...` (viết tài liệu)
  - `refactor: ...` (tối ưu mã nguồn)