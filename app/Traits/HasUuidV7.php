<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

trait HasUuidV7
{
    use HasUuids;

    /**
     * Tự động sinh chuỗi UUID version 7 (RFC 9562) cho Model khi tạo mới bản ghi.
     * UUID v7 chứa Unix timestamp ở phần đầu, giúp sắp xếp tuần tự theo thời gian
     * và tối ưu hóa hiệu năng đánh chỉ mục B-Tree trong CSDL (MySQL / MariaDB).
     */
    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }
}
