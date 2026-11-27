<?php

namespace App\Helpers;

use GuzzleHttp\Promise\Utils;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Tiện ích tải tệp theo lô (Batch Storage Upload) dùng chung cho các Service.
 *
 * Thống nhất một cơ chế duy nhất cho cả 1 tệp lẫn nhiều tệp:
 * - Khi disk đích dùng driver S3 (Supabase S3-Compatible API), thực thi đẩy song song qua S3Client::uploadAsync().
 * - Tự động fallback từng tệp bị lỗi kết nối về ổ đĩa cục bộ để không làm gián đoạn cả lô.
 */
class StorageUploadHelper
{
    /**
     * Tải lên danh sách tệp tin lên Storage đích (ưu tiên Supabase S3 song song) với cơ chế fallback cục bộ.
     *
     * @param array<int|string, array{file: mixed, path: string, fallback_dir: string, filename: string}> $items
     * @param string $diskName Tên disk đích trong config/filesystems.php ('supabase' hoặc 'supabase_private')
     * @param string $fallbackDisk Disk dự phòng ('public' hoặc 'local')
     * @param string $visibility Quyền hiển thị ('public' hoặc 'private')
     * @return array<int|string, array{path: string, url: string, original_name: string, mime: string}>
     */
    public static function uploadBatch(
        array $items,
        string $diskName,
        string $fallbackDisk = 'public',
        string $visibility = 'public'
    ): array {
        if (empty($items)) {
            return [];
        }

        $results = [];
        $uploadItems = [];

        foreach ($items as $key => $item) {
            $file = $item['file'] ?? null;

            if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
                $results[$key] = [
                    'path' => $file,
                    'url' => $file,
                    'original_name' => basename($file),
                    'mime' => 'image/jpeg',
                ];
                continue;
            }

            if ($file instanceof UploadedFile) {
                $uploadItems[$key] = $item;
            }
        }

        if (empty($uploadItems)) {
            return $results;
        }

        $hasRemoteDisk = !empty(config("filesystems.disks.{$diskName}.key"));

        if (!$hasRemoteDisk) {
            foreach ($uploadItems as $key => $item) {
                $results[$key] = self::storeLocally($item, $fallbackDisk);
            }

            return $results;
        }

        $disk = Storage::disk($diskName);

        // Thống nhất một cơ chế uploadAsync cho mọi số lượng tệp (1 hay nhiều tệp) khi kết nối driver S3 thật
        if ($disk instanceof AwsS3V3Adapter) {
            $bucket = (string) config("filesystems.disks.{$diskName}.bucket");
            $s3Client = $disk->getClient();
            $acl = $visibility === 'public' ? 'public-read' : 'private';

            $promises = [];
            $streams = [];
            $meta = [];

            try {
                foreach ($uploadItems as $key => $item) {
                    /** @var UploadedFile $file */
                    $file = $item['file'];
                    $mime = self::resolveMimeType($file);
                    $realPath = $file->getRealPath() ?: $file->getPathname();
                    $stream = @fopen($realPath, 'r');

                    if (!is_resource($stream)) {
                        Log::warning("Không thể mở luồng đọc tệp [{$item['filename']}], chuyển sang lưu cục bộ.");
                        $results[$key] = self::storeLocally($item, $fallbackDisk);
                        continue;
                    }

                    $streams[] = $stream;
                    $meta[$key] = [
                        'path' => $item['path'],
                        'original_name' => $file->getClientOriginalName(),
                        'mime' => $mime,
                    ];

                    $promises[$key] = $s3Client->uploadAsync(
                        $bucket,
                        $item['path'],
                        $stream,
                        $acl,
                        ['params' => ['ContentType' => $mime]]
                    );
                }

                if (!empty($promises)) {
                    $settled = Utils::settle($promises)->wait();

                    foreach ($settled as $key => $outcome) {
                        if (($outcome['state'] ?? null) === 'fulfilled') {
                            $remotePath = $meta[$key]['path'];
                            $results[$key] = [
                                'path' => $remotePath,
                                'url' => $visibility === 'public' ? $disk->url($remotePath) : $remotePath,
                                'original_name' => $meta[$key]['original_name'],
                                'mime' => $meta[$key]['mime'],
                            ];
                        } else {
                            $reason = $outcome['reason'] ?? null;
                            $errMsg = $reason instanceof \Throwable ? $reason->getMessage() : 'Unknown async upload error';
                            Log::warning("Supabase async upload failed ({$diskName}), fallback to local storage: " . $errMsg);
                            $results[$key] = self::storeLocally($uploadItems[$key], $fallbackDisk);
                        }
                    }
                }
            } finally {
                foreach ($streams as $stream) {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }

            return $results;
        }

        // Nhánh cho FilesystemAdapter cục bộ (khi driver của disk không phải S3)
        foreach ($uploadItems as $key => $item) {
            /** @var UploadedFile $file */
            $file = $item['file'];
            $mime = self::resolveMimeType($file);
            $stream = null;

            try {
                $realPath = $file->getRealPath() ?: $file->getPathname();
                $stream = @fopen($realPath, 'r');

                $disk->put($item['path'], is_resource($stream) ? $stream : file_get_contents($realPath), [
                    'mimetype' => $mime,
                    'visibility' => $visibility,
                ]);

                $results[$key] = [
                    'path' => $item['path'],
                    'url' => $visibility === 'public' ? $disk->url($item['path']) : $item['path'],
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $mime,
                ];
            } catch (\Throwable $e) {
                Log::warning("Supabase upload failed ({$diskName}), fallback to local storage: " . $e->getMessage());
                $results[$key] = self::storeLocally($item, $fallbackDisk);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        return $results;
    }

    /**
     * Lưu trữ tệp dự phòng vào ổ đĩa cục bộ khi chưa cấu hình hoặc lỗi kết nối Storage từ xa.
     *
     * @param array{file: UploadedFile, path: string, fallback_dir: string, filename: string} $item
     * @param string $fallbackDisk
     * @return array{path: string, url: string, original_name: string, mime: string}
     */
    protected static function storeLocally(array $item, string $fallbackDisk): array
    {
        /** @var UploadedFile $file */
        $file = $item['file'];
        $dir = trim($item['fallback_dir'], '/');
        $filename = $item['filename'];

        if ($fallbackDisk === 'public') {
            $localPath = $file->storeAs($dir, $filename);
            $url = Storage::url($localPath);
        } else {
            $localPath = $file->storeAs($dir, $filename, $fallbackDisk);
            $url = $localPath;
        }

        return [
            'path' => (string) $localPath,
            'url' => (string) $url,
            'original_name' => $file->getClientOriginalName(),
            'mime' => self::resolveMimeType($file),
        ];
    }

    /**
     * Xác định MIME type an toàn của tệp tải lên.
     */
    protected static function resolveMimeType(UploadedFile $file): string
    {
        $mime = method_exists($file, 'getClientMimeType') ? $file->getClientMimeType() : null;
        if (empty($mime)) {
            $mime = $file->getMimeType();
        }

        return $mime ?: 'application/octet-stream';
    }
}
