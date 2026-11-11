<?php

return [
    'default' => 'default',

    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Tài liệu API Hệ Thống Cho Thuê Văn Phòng (OpenAPI 3.0)',
            ],

            'routes' => [
                // Đường dẫn truy cập giao diện Swagger UI
                'api' => 'api/documentation',
            ],

            'paths' => [
                // Sử dụng URL tuyệt đối cho assets trên giao diện
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                // Đường dẫn chứa tài nguyên tĩnh của Swagger UI
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

                // Tên file JSON tài liệu được tạo ra
                'docs_json' => 'api-docs.json',

                // Tên file YAML tài liệu
                'docs_yaml' => 'api-docs.yaml',

                // Định dạng hiển thị mặc định trên UI: 'json' hoặc 'yaml'
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                // Thư mục mã nguồn cần quét các Annotation / Attribute OpenAPI
                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],

    'defaults' => [
        'routes' => [
            // Đường dẫn trả về file JSON tài liệu thô
            'docs' => 'docs',

            // Đường dẫn callback xác thực OAuth2
            'oauth2_callback' => 'api/oauth2-callback',

            // Middleware bảo vệ tài liệu API (để trống: công khai, hoặc dùng ['auth:sanctum'] trên production)
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],

            // Tùy chọn gom nhóm route (Group Options)
            'group_options' => [],
        ],

        'paths' => [
            // Thư mục lưu trữ file tài liệu sinh ra
            'docs' => storage_path('api-docs'),

            // Thư mục views của Swagger UI
            'views' => base_path('resources/views/vendor/l5-swagger'),

            // Base path API (nếu có tiền tố đặc biệt)
            'base' => env('L5_SWAGGER_BASE_PATH', null),

            // Các thư mục loại trừ không quét
            'excludes' => [],
        ],

        'scanOptions' => [
            // Chế độ quét: 'classic', 'hybrid' hoặc 'spec'
            'mode' => env('L5_SWAGGER_SCAN_MODE', 'classic'),

            // Factory tùy biến generator (mặc định null)
            'generator_factory' => null,

            // Factory tùy biến builder (mặc định null)
            'builder_factory' => null,

            // Cấu hình các bộ xử lý mặc định
            'default_processors_configuration' => [],

            // Analyser tĩnh phân tích cú pháp
            'analyser' => null,

            // Phân tích cú pháp (mặc định null)
            'analysis' => null,

            // Bộ xử lý tùy biến
            'processors' => [],

            // Mẫu file quét (mặc định null = *.php)
            'pattern' => null,

            // Thư mục loại trừ khỏi quá trình quét
            'exclude' => [],

            // Phiên bản chuẩn OpenAPI: 3.0.0 hoặc 3.1.0
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION),
        ],

        // Cấu hình bảo mật (Token / Bearer / OAuth2)
        'securityDefinitions' => [
            'securitySchemes' => [],
            'security' => [],
        ],

        // Luôn tự động quét & sinh lại tài liệu mỗi khi có request (khuyên dùng khi Dev)
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),

        // Tự động sinh thêm bản sao định dạng YAML
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        // Cấu hình Proxy (dành cho môi trường chạy qua Load Balancer / Nginx ngược)
        'proxy' => false,

        // Đường dẫn file cấu hình Swagger UI bên ngoài
        'additional_config_url' => null,

        // Sắp xếp danh sách endpoint: 'alpha' (A-Z) hoặc 'method' (GET/POST/PUT/DELETE)
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),

        // URL validator schema trực tuyến (null để tắt)
        'validator_url' => null,

        'ui' => [
            'display' => [
                // Giao diện nền tối (Dark mode)
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),

                // Mức độ mở rộng ban đầu: 'none' (thu gọn), 'list' (hiện tags), 'full' (mở tất cả)
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),

                // Ô tìm kiếm / lọc endpoint trên giao diện
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),
            ],

            'authorization' => [
                // Giữ lại Token xác thực khi tải lại hoặc đóng trình duyệt
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),

                'oauth2' => [
                    // Sử dụng PKCE cho luồng Authorization Code Grant
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],

        // Các hằng số dùng trong Annotation
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://my-default-host.com'),
        ],
    ],
];
