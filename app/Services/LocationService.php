<?php

namespace App\Services;

use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocationService
{
    /**
     * Đường dẫn thư mục lưu cache geo JSON trong storage/app/geo.
     */
    protected const GEO_STORAGE_DIR = 'geo';

    /**
     * Bảng ánh xạ mã Tỉnh v2 (34 tỉnh thành sau sáp nhập) sang danh sách mã Tỉnh tương ứng trong API v1.
     * Cho phép nạp toàn bộ danh mục Quận / Huyện chính thức từ API v1.
     *
     * @var array<int, list<int>>
     */
    public const V2_TO_V1_PROVINCE_CODES = [
        1  => [1],       // Thành phố Hà Nội
        4  => [4],       // Tỉnh Cao Bằng
        8  => [8],       // Tỉnh Tuyên Quang
        11 => [11],     // Tỉnh Điện Biên
        12 => [12],     // Tỉnh Lai Châu
        14 => [14],     // Tỉnh Sơn La
        15 => [10, 15], // Tỉnh Lào Cai (+ Yên Bái)
        19 => [19],     // Tỉnh Thái Nguyên
        20 => [20],     // Tỉnh Lạng Sơn
        22 => [22],     // Thành phố Quảng Ninh
        24 => [27, 24], // Thành phố Bắc Ninh (gồm Bắc Ninh [27] và Bắc Giang [24])
        25 => [25],     // Tỉnh Phú Thọ
        31 => [31],     // Thành phố Hải Phòng
        33 => [33],     // Tỉnh Hưng Yên
        37 => [37],     // Tỉnh Ninh Bình
        38 => [38],     // Tỉnh Thanh Hóa
        40 => [40],     // Tỉnh Nghệ An
        42 => [42],     // Tỉnh Hà Tĩnh
        44 => [45, 44], // Tỉnh Quảng Trị (+ Quảng Bình)
        46 => [46],     // Thành phố Huế
        48 => [48],     // Thành phố Đà Nẵng
        51 => [51],     // Tỉnh Quảng Ngãi
        52 => [64, 52], // Tỉnh Gia Lai (+ Bình Định)
        56 => [56],     // Tỉnh Khánh Hòa
        66 => [66],     // Tỉnh Đắk Lắk
        68 => [68],     // Tỉnh Lâm Đồng
        75 => [75],     // Thành phố Đồng Nai
        79 => [79],     // Thành phố Hồ Chí Minh
        80 => [72, 80], // Tỉnh Tây Ninh (+ Long An)
        82 => [87, 82], // Tỉnh Đồng Tháp (+ Tiền Giang)
        86 => [86],     // Tỉnh Vĩnh Long
        91 => [89, 91], // Tỉnh An Giang (+ Kiên Giang)
        92 => [92],     // Thành phố Cần Thơ
        96 => [96],     // Tỉnh Cà Mau
    ];

    /**
     * Lấy toàn bộ dữ liệu địa giới (danh sách Phường Mới kèm tham chiếu Quận cũ và Khu vực) của một Tỉnh.
     * - Nếu file storage/app/geo/province_{code}.json đã có và hợp lệ: Đọc trực tiếp (0ms delay).
     * - Nếu chưa có: Tự động gọi API v2 chính thức, nạp danh mục Quận/Huyện từ API v1 và lưu file cache.
     *
     * @param int $provinceCode Mã tỉnh chuẩn (VD: 79 - TP.HCM, 1 - Hà Nội, 24 - Bắc Ninh)
     * @return array<string, mixed>
     */
    public function getProvinceGeoData(int $provinceCode): array
    {
        $fileName = self::GEO_STORAGE_DIR . "/province_{$provinceCode}.json";

        // 1. Kiểm tra file đã tồn tại trong local storage chưa (yêu cầu có danh sách areas)
        if (Storage::disk('local')->exists($fileName)) {
            $content = Storage::disk('local')->get($fileName);
            $decoded = json_decode($content, true);
            if (is_array($decoded) && !empty($decoded['areas'])) {
                return $decoded;
            }
        }

        if (app()->environment('testing') && !Http::isFaking()) {
            return [];
        }

        // 2. Chưa có trong cache hoặc cache chưa có areas -> Gọi API v2 chính thức
        try {
            $response = Http::timeout(15)->withoutVerifying()->get("https://provinces.open-api.vn/api/v2/p/{$provinceCode}", [
                'depth' => 2,
            ]);

            if ($response->successful()) {
                $raw = $response->json();
                $processed = $this->transformAndEnrichProvinceData($raw);

                // Lưu file vào storage/app/geo/
                Storage::disk('local')->put(
                    $fileName,
                    json_encode($processed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                return $processed;
            }
        } catch (\Throwable $e) {
            Log::error("LocationService: Lỗi tải dữ liệu địa lý cho tỉnh {$provinceCode}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Bộ nhớ đệm danh mục Quận/Huyện theo từng tỉnh (Code => Tên) để tránh gọi lặp lại.
     *
     * @var array<int, array<int, string>>
     */
    protected array $districtMemoryCache = [];

    /**
     * - Duyệt từng Phường Mới.
     * - Tự động tra cứu danh mục Quận/Huyện cũ của Tỉnh đó từ API (hoặc file dự phòng).
     * - Lấy tham chiếu các Phường/Quận cũ cấu thành (legacies).
     * - Tạo nhãn hiển thị: "Phường Mới (Tên Quận)" hoàn toàn tự động, KHÔNG hardcode.
     * - Gom nhóm danh sách "Khu Vực" (Quận 1, Quận 2, Quận 3...).
     *
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    public function transformAndEnrichProvinceData(array $raw): array
    {
        $provinceCode = (int) $raw['code'];
        $wards = $raw['wards'] ?? [];
        $enrichedWards = [];
        $areasSet = [];

        // Nạp danh bạ Quận/Huyện của Tỉnh này hoàn toàn động từ API v1
        $districtMap = $this->getLegacyDistrictsMap($provinceCode);

        foreach ($wards as $w) {
            $wardCode = (int) $w['code'];
            $wardName = $w['name'];

            $districtHint = null;
            if (!empty($w['district_code']) && isset($districtMap[(int) $w['district_code']])) {
                $districtHint = $districtMap[(int) $w['district_code']];
            }

            // Tạo nhãn hiển thị chuẩn: "Phường Sài Gòn (Quận 1)" hoặc giữ nguyên nếu không có hint
            $displayLabel = $districtHint ? "{$wardName} ({$districtHint})" : $wardName;

            if ($districtHint) {
                $areasSet[$districtHint] = true;
            }

            $enrichedWards[] = [
                'code' => $wardCode,
                'name' => $wardName,
                'codename' => $w['codename'] ?? null,
                'division_type' => $w['division_type'] ?? 'phường',
                'province_code' => (int) ($w['province_code'] ?? $raw['code']),
                'district_hint' => $districtHint,
                'display_label' => $displayLabel,
                'slug' => Str::slug($wardName),
                'legacies' => [],
            ];
        }

        // Danh sách Quận / Huyện: Kết hợp danh mục quận/huyện từ API v1 và gợi ý từ các phường
        $allDistricts = array_values(array_unique(array_merge(array_keys($areasSet), array_values($districtMap))));

        return [
            'province_code' => $provinceCode,
            'province_name' => $raw['name'],
            'division_type' => $raw['division_type'] ?? null,
            'codename' => $raw['codename'] ?? null,
            'phone_code' => $raw['phone_code'] ?? null,
            'areas' => $allDistricts,
            'wards' => $enrichedWards,
        ];
    }

    /**
     * Lấy danh bạ ánh xạ [Mã quận => Tên quận] một cách hoàn toàn ĐỘNG:
     * Tự động tra cứu mã Tỉnh v1 qua bảng ánh xạ V2_TO_V1_PROVINCE_CODES (hoặc mã gốc)
     * và gọi API v1 (/api/v1/p/{code}?depth=2) để tải danh sách quận/huyện chuẩn.
     * Áp dụng thống nhất cho tất cả 34 tỉnh thành sau sáp nhập.
     *
     * @param int $provinceCode
     * @return array<int, string>
     */
    public function getLegacyDistrictsMap(int $provinceCode): array
    {
        if (isset($this->districtMemoryCache[$provinceCode])) {
            return $this->districtMemoryCache[$provinceCode];
        }

        $map = [];
        $v1Codes = self::V2_TO_V1_PROVINCE_CODES[$provinceCode] ?? [$provinceCode];

        foreach ($v1Codes as $v1Code) {
            try {
                $res = Http::timeout(10)->withoutVerifying()->get("https://provinces.open-api.vn/api/v1/p/{$v1Code}?depth=2");
                if ($res->successful()) {
                    $districts = $res->json('districts', []);
                    foreach ($districts as $d) {
                        $map[(int) $d['code']] = $d['name'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("LocationService: Không thể tải danh sách quận/huyện từ API v1 cho tỉnh {$v1Code}: " . $e->getMessage());
            }
        }

        $this->districtMemoryCache[$provinceCode] = $map;

        return $map;
    }

    /**
     * Lấy danh sách tên Quận / Huyện của một Tỉnh.
     * Tự động đọc từ file cache geo nếu có, hoặc gọi API để tạo và lưu file.
     *
     * @param int $provinceCode
     * @return list<string>
     */
    public function getDistricts(int $provinceCode): array
    {
        $geo = $this->getProvinceGeoData($provinceCode);
        return $geo['areas'] ?? [];
    }

    /**
     * Gọi endpoint /api/v2/w/{code}/to-legacies/ để lấy danh sách phường cũ đã sáp nhập.
     * Tên quận được tra cứu động qua $districtMap, nếu chưa có sẽ query trực tiếp API quận (/api/v1/d/{code}).
     *
     * @param int $wardCode
     * @param array<int, string> $districtMap
     * @return list<array<string, mixed>>
     */
    public function fetchWardLegacies(int $wardCode, array $districtMap = []): array
    {
        try {
            $res = Http::timeout(5)->withoutVerifying()->get("https://provinces.open-api.vn/api/v2/w/{$wardCode}/to-legacies/");
            if ($res->successful()) {
                $items = $res->json();
                if (is_array($items)) {
                    $legacies = [];
                    foreach ($items as $item) {
                        $distCode = (int) ($item['district_code'] ?? 0);
                        $districtName = $districtMap[$distCode] ?? $this->resolveDistrictNameDynamically($distCode);

                        $legacies[] = [
                            'code' => (int) $item['code'],
                            'name' => $item['name'],
                            'district_code' => $distCode ?: null,
                            'district_name' => $districtName,
                        ];
                    }
                    return $legacies;
                }
            }
        } catch (\Throwable $e) {
            // Không ngắt dòng nếu API legacy timeout
        }

        return [];
    }

    /**
     * Tra cứu tên Quận/Huyện đơn lẻ qua API v1 hoàn toàn tự động khi chưa có trong danh bạ.
     *
     * @param int $districtCode
     * @return string|null
     */
    protected function resolveDistrictNameDynamically(int $districtCode): ?string
    {
        if ($districtCode <= 0) {
            return null;
        }

        try {
            $res = Http::timeout(3)->withoutVerifying()->get("https://provinces.open-api.vn/api/v1/d/{$districtCode}");
            if ($res->successful()) {
                return $res->json('name');
            }
        } catch (\Throwable $e) {}

        return null;
    }

    /**
     * Lấy danh sách các Tỉnh/Thành phố đang có phòng trống (AVAILABLE) cho thuê.
     * Kết quả được Cache 1 giờ để tối ưu tài nguyên DB.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProvincesWithAvailableUnits(): array
    {
        return Cache::remember('provinces_with_available_units', 3600, function () {
            return Province::hasAvailableUnits()->orderBy('name')->get()->toArray();
        });
    }

    /**
     * Xóa cache file JSON của một tỉnh (dùng khi cần đồng bộ lại).
     */
    public function clearProvinceCache(int $provinceCode): bool
    {
        $fileName = self::GEO_STORAGE_DIR . "/province_{$provinceCode}.json";
        if (Storage::disk('local')->exists($fileName)) {
            return Storage::disk('local')->delete($fileName);
        }
        return false;
    }
}
