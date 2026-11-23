<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Leasing & Commercial Real Estate Financial Configuration
    |--------------------------------------------------------------------------
    |
    | Tập trung quản lý toàn bộ các tham số tài chính, chính sách thuế và quy chuẩn
    | vận hành cho thuê bất động sản thương mại theo nguyên lý Clean Architecture.
    | Tránh hoàn toàn việc hard-code "magic numbers" trong mã nguồn.
    |
    */

    // Thuế suất GTGT chuẩn (Mặc định 10% theo Luật Thuế GTGT và Thông tư 219/2013/TT-BTC)
    // Có thể điều chỉnh thành 0.08 khi có Nghị quyết kích cầu giảm thuế của Quốc hội.
    'vat_rate' => (float) env('LEASING_VAT_RATE', 0.10),

    // Số tháng tiền cọc bảo đảm quy chuẩn thị trường văn phòng thương mại (CBRE / Savills)
    'deposit_months' => (int) env('LEASING_DEPOSIT_MONTHS', 3),

    // Chu kỳ thanh toán mặc định (tháng/lần)
    'default_payment_cycle' => (int) env('LEASING_DEFAULT_PAYMENT_CYCLE', 1),

    // Hạn nộp tiền thanh toán định kỳ hàng tháng (ngày trong tháng, từ 1 đến 28)
    'default_payment_due_day' => (int) env('LEASING_DEFAULT_PAYMENT_DUE_DAY', 5),

    // Đơn giá phí dịch vụ sàn cơ sở mặc định (VNĐ/m²/tháng)
    'default_service_fee_sqm' => (float) env('LEASING_DEFAULT_SERVICE_FEE_SQM', 150000),

];
