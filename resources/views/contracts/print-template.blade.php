<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp Đồng Thuê Văn Phòng - {{ $contract->contract_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           1. CẤU HÌNH KHỔ GIẤY IN A4 VECTOR TIÊU CHUẨN PHÁP LÝ VIỆT NAM
           ========================================================================== */
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 20mm; /* Top Right Bottom Left theo Nghị định 30/2020/NĐ-CP */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Times New Roman', Times, serif;
            font-size: 13.5pt;
            line-height: 1.45;
            color: #000000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Khung trang hiển thị preview trên màn hình */
        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 20mm 15mm 20mm 20mm;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* ==========================================================================
           2. THANH CÔNG CỤ XUẤT PDF / IN (ẨN KHI IN THỰC TẾ)
           ========================================================================== */
        .floating-action-bar {
            position: fixed;
            top: 16px;
            right: 24px;
            display: flex;
            gap: 10px;
            z-index: 99999;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            padding: 10px 16px;
            border-radius: 50px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            font-family: 'Inter', sans-serif;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-pdf {
            background: #ea580c;
            color: #ffffff;
        }

        .btn-pdf:hover {
            background: #c2410c;
            transform: translateY(-1px);
        }

        .btn-close {
            background: #334155;
            color: #f8fafc;
        }

        .btn-close:hover {
            background: #475569;
        }

        /* ==========================================================================
           3. NỘI DUNG VĂN BẢN HỢP ĐỒNG CHUẨN PHÁP LÝ
           ========================================================================== */
        .contract-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .republic-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .motto {
            font-size: 13pt;
            font-weight: bold;
            margin-top: 3px;
        }

        .header-line {
            width: 140px;
            height: 1px;
            background: #000000;
            margin: 6px auto 18px auto;
        }

        .contract-title {
            font-size: 17pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 4px;
        }

        .contract-number {
            font-size: 12pt;
            font-style: italic;
            margin-bottom: 15px;
        }

        .contract-intro {
            font-style: italic;
            text-align: justify;
            margin-bottom: 16px;
        }

        .party-block {
            margin-bottom: 16px;
            text-align: justify;
        }

        .party-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13.5pt;
            margin-bottom: 4px;
            text-decoration: underline;
        }

        .party-row {
            margin-bottom: 3px;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 16px;
            margin-bottom: 6px;
            font-size: 13.5pt;
        }

        .clause-p {
            text-align: justify;
            margin-bottom: 6px;
            text-indent: 25px;
        }

        .clause-p-no-indent {
            text-align: justify;
            margin-bottom: 6px;
        }

        /* Bảng danh sách mặt bằng */
        .contract-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 0;
            font-size: 12.5pt;
            page-break-inside: avoid;
        }

        .contract-table th,
        .contract-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .contract-table th {
            background-color: #f8fafc;
            font-weight: bold;
            text-align: center;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Chữ ký hai bên */
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .sign-box {
            width: 48%;
            text-align: center;
        }

        .sign-role {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13pt;
        }

        .sign-note {
            font-style: italic;
            font-size: 11pt;
            margin-top: 2px;
            margin-bottom: 75px;
        }

        .sign-name {
            font-weight: bold;
            font-size: 13pt;
        }

        /* ==========================================================================
           4. QUY TẮC KHI IN (MEDIA PRINT)
           ========================================================================== */
        @media print {
            body {
                background: #ffffff;
            }

            .floating-action-bar {
                display: none !important;
            }

            .page-container {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <!-- THANH CÔNG CỤ NỔI BẬT: NÚT XUẤT PDF & IN -->
    <div class="floating-action-bar">
        <button type="button" onclick="triggerPrint()" class="btn-action btn-pdf">
            <i class="fa-solid fa-file-pdf"></i> Xuất PDF
        </button>
        <button type="button" onclick="window.close()" class="btn-action btn-close">
            <i class="fa-solid fa-xmark"></i> Đóng
        </button>
    </div>

    <!-- KHUNG HỢP ĐỒNG PHÁP LÝ A4 -->
    <div class="page-container">
        
        <!-- QUỐC HIỆU & TIÊU NGỮ -->
        <div class="contract-header">
            <div class="republic-title">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="motto">Độc lập - Tự do - Hạnh phúc</div>
            <div class="header-line"></div>

            <div class="contract-title">
                @if($is_booking ?? false)
                    THỎA THUẬN ĐẶT CỌC GIỮ CHỖ MẶT BẰNG VĂN PHÒNG
                @elseif(($contract->status?->value ?? $contract->status) === 'DRAFT')
                    HỢP ĐỒNG THUÊ VĂN PHÒNG (BẢN DỰ THẢO)
                @else
                    HỢP ĐỒNG THUÊ VĂN PHÒNG
                @endif
            </div>
            <div class="contract-number">Số: <strong>{{ $contract->contract_number }}</strong>
                @if($is_booking ?? false)
                    <span style="font-size:10pt;font-weight:normal;color:#b45309;margin-left:8px;">[ĐẶT CỌC GIỮ CHỖ]</span>
                @elseif(($contract->status?->value ?? $contract->status) === 'DRAFT')
                    <span style="font-size:10pt;font-weight:normal;color:#0284c7;margin-left:8px;">[DỰ THẢO TRÌNH DUYỆT]</span>
                @endif
            </div>
        </div>

        <div class="contract-intro">
            Hôm nay, ngày {{ $signed_day }} tháng {{ $signed_month }} năm {{ $signed_year }}, tại địa chỉ Tòa nhà {{ $contract->building?->name }}, chúng tôi gồm các bên dưới đây:
        </div>

        <!-- BÊN A -->
        <div class="party-block">
            <div class="party-title">BÊN CHO THUÊ (BÊN A):</div>
            <div class="party-row"><strong>Đơn vị:</strong> {{ $party_a['company_name'] }}</div>
            <div class="party-row"><strong>Địa chỉ:</strong> {{ $party_a['address'] }}</div>
            <div class="party-row"><strong>Mã số thuế:</strong> {{ $party_a['tax_code'] }}</div>
            <div class="party-row"><strong>Người đại diện:</strong> {{ $party_a['representative'] }} &nbsp;&nbsp;&nbsp;&nbsp; <strong>Chức vụ:</strong> {{ $party_a['position'] }}</div>
            <div class="party-row"><strong>Điện thoại liên hệ:</strong> {{ $party_a['phone'] }}</div>
            <div class="party-row"><strong>Tài khoản thanh toán:</strong> {{ $party_a['bank_account'] }}</div>
        </div>

        <!-- BÊN B -->
        <div class="party-block">
            <div class="party-title">BÊN THUÊ (BÊN B):</div>
            <div class="party-row"><strong>Tên doanh nghiệp:</strong> {{ $party_b['company_name'] }}</div>
            <div class="party-row"><strong>Địa chỉ trụ sở chính:</strong> {{ $party_b['address'] }}</div>
            <div class="party-row"><strong>Mã số doanh nghiệp / MST:</strong> {{ $party_b['tax_code'] }}</div>
            <div class="party-row"><strong>Người đại diện theo pháp luật:</strong> {{ $party_b['representative'] }} &nbsp;&nbsp;&nbsp;&nbsp; <strong>Chức vụ:</strong> {{ $party_b['position'] }}</div>
            <div class="party-row"><strong>Số điện thoại:</strong> {{ $party_b['phone'] }} &nbsp;&nbsp;&nbsp;&nbsp; <strong>Email nhận hóa đơn:</strong> {{ $party_b['email'] }}</div>
        </div>

        <div class="clause-p-no-indent" style="margin-bottom: 14px;">
            Sau khi thỏa thuận trên tinh thần tự nguyện, bình đẳng và cùng có lợi, hai Bên thống nhất ký kết Hợp đồng thuê văn phòng (sau đây gọi tắt là "Hợp đồng") với các điều khoản và điều kiện cụ thể sau đây:
        </div>

        <!-- ĐIỀU 1 -->
        <div class="section-title">ĐIỀU 1: ĐỐI TƯỢNG HỢP ĐỒNG & MẶT BẰNG THUÊ</div>
        <div class="clause-p">
            1.1. Bên A đồng ý cho Bên B thuê và Bên B đồng ý thuê phần diện tích sàn văn phòng thuộc Tòa nhà <strong>{{ $contract->building?->name }}</strong>, tọa lạc tại địa chỉ: {{ $party_a['address'] }}.
        </div>
        <div class="clause-p">
            1.2. Danh sách chi tiết các mặt bằng văn phòng được hai bên thống nhất đưa vào hợp đồng thuê:
        </div>

        <table class="contract-table">
            <thead>
                <tr>
                    <th style="width: 8%;">STT</th>
                    <th style="width: 20%;">Mã Mặt Bằng</th>
                    <th style="width: 14%;">Vị Trí Tầng</th>
                    <th style="width: 18%;">Diện Tích (m²)</th>
                    <th style="width: 20%;">Đơn Giá Thuê (VNĐ/m²)</th>
                    <th style="width: 20%;">Phí Quản Lý DV (VNĐ/m²)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contract_units as $index => $cu)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center"><strong>{{ $cu->unit?->unit_code }}</strong></td>
                        <td class="text-center">Tầng {{ $cu->unit?->floor_number }}</td>
                        <td class="text-right">{{ number_format($cu->leased_area_sqm, 2) }}</td>
                        <td class="text-right">{{ number_format($cu->agreed_rental_price_per_sqm, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($cu->agreed_service_fee_per_sqm, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr style="background:#f8fafc;">
                    <td colspan="3" class="font-bold text-center">TỔNG CỘNG DIỆN TÍCH THUÊ</td>
                    <td class="font-bold text-right">{{ number_format($contract->total_leased_area_sqm, 2) }} m²</td>
                    <td colspan="2" class="text-center font-bold" style="font-size:11pt;color:#334155;">Đã chốt thỏa thuận</td>
                </tr>
            </tbody>
        </table>

        <div class="clause-p">
            1.3. Mục đích thuê: Bên B chỉ được sử dụng phần diện tích nói trên để làm văn phòng làm việc và địa điểm giao dịch kinh doanh hợp pháp theo ngành nghề đã đăng ký.
        </div>

        <!-- ĐIỀU 2 -->
        <div class="section-title">ĐIỀU 2: THỜI HẠN THUÊ & BÀN GIAO MẶT BẰNG</div>
        <div class="clause-p">
            2.1. Thời hạn thuê là <strong>{{ $lease_duration_months }} tháng</strong> (tương đương {{ round($lease_duration_months / 12, 1) }} năm), bắt đầu tính từ ngày <strong>{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</strong> đến hết ngày <strong>{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</strong>.
        </div>
        <div class="clause-p">
            2.2. Thời gian bàn giao mặt bằng: Bên A bàn giao hiện trạng mặt bằng thực tế cho Bên B vào trước hoặc đúng ngày bắt đầu thời hạn thuê. Hai bên sẽ tiến hành lập Biên bản bàn giao hiện trạng trang thiết bị gắn liền với sàn văn phòng.
        </div>
        <div class="clause-p">
            2.3. Quyền ưu tiên gia hạn: Khi kết thúc thời hạn thuê theo Hợp đồng này, nếu Bên B có nhu cầu tiếp tục thuê và tuân thủ tốt các nghĩa vụ hợp đồng thì Bên B sẽ được quyền ưu tiên gia hạn hợp đồng. Bên B phải gửi thông báo bằng văn bản cho Bên A trước ít nhất 60 (sáu mươi) ngày trước khi Hợp đồng hết hạn.
        </div>

        <!-- ĐIỀU 3 -->
        <div class="section-title">ĐIỀU 3: GIÁ THUÊ, PHÍ DỊCH VỤ & PHƯƠNG THỨC THANH TOÁN</div>
        <div class="clause-p">
            3.1. <strong>Tiền thuê văn phòng hàng tháng:</strong>
            {{ number_format($monthly_rent, 0, ',', '.') }} VNĐ/tháng.
            <br>
            <em>(Bằng chữ: {{ $monthly_rent_words }}).</em>
        </div>
        <div class="clause-p">
            3.2. <strong>Phí quản lý và dịch vụ vận hành tòa nhà:</strong>
            {{ number_format($monthly_service_fee, 0, ',', '.') }} VNĐ/tháng.
            <br>
            <em>(Bằng chữ: {{ $monthly_service_fee_words }}).</em>
        </div>
        <div class="clause-p">
            3.3. <strong>Tổng chi phí tiền thuê và phí dịch vụ hàng tháng:</strong>
            <strong>{{ number_format($monthly_total, 0, ',', '.') }} VNĐ/tháng</strong>.
            <br>
            <em>(Bằng chữ: <strong>{{ $monthly_total_words }}</strong>).</em>
        </div>
        <div class="clause-p">
            3.4. <strong>Kỳ hạn thanh toán:</strong> Bên B thanh toán định kỳ <strong>{{ $contract->payment_cycle_months }} tháng / lần</strong>. Thời hạn thanh toán chậm nhất vào <strong>ngày {{ $contract->payment_due_day }}</strong> của tháng đầu tiên trong mỗi kỳ thanh toán.
        </div>
        <div class="clause-p">
            3.5. <strong>Tiền điện, nước, gửi xe và dịch vụ ngoài giờ:</strong> Được tính theo chỉ số đồng hồ thực tế tiêu thụ và bảng giá niêm yết của tòa nhà, Bên B có trách nhiệm thanh toán sau khi nhận được Bảng thông báo cước phí (Debit Note) định kỳ hàng tháng từ Bên A.
        </div>
        <div class="clause-p">
            3.6. <strong>Lãi suất phạt chậm thanh toán:</strong> Nếu Bên B chậm thanh toán tiền thuê hoặc các khoản phí dịch vụ quá thời hạn quy định, Bên B phải chịu mức phạt lãi suất là <strong>{{ $contract->late_fee_interest_percent }}%/ngày</strong> tính trên tổng số tiền chậm nộp cho mỗi ngày quá hạn.
        </div>
        <div class="clause-p">
            3.7. <strong>Tỷ lệ trượt giá thuê hàng năm:</strong> Đơn giá thuê nêu tại Điều 3.1 được giữ cố định trong 12 tháng đầu tiên. Từ năm thứ hai trở đi, đơn giá thuê sẽ được điều chỉnh tăng với tỷ lệ <strong>{{ $contract->annual_price_escalation_percent }}%/năm</strong>.
        </div>

        <!-- ĐIỀU 4 -->
        <div class="section-title">ĐIỀU 4: TIỀN ĐẶT CỌC & GIỮ CHỖ MẶT BẰNG</div>
        @if(($is_booking ?? false) && ($booking_deposit_amount ?? 0) > 0)
        <div class="clause-p" style="background:#fffbeb;border:1px solid #fef3c7;padding:10px 14px;border-radius:4px;margin-bottom:10px;">
            <strong>4.1. Tiền đặt cọc giữ chỗ tạm thời (Booking Deposit):</strong> 
            Bên B đã nộp số tiền đặt cọc giữ chỗ là: <strong>{{ number_format($booking_deposit_amount, 0, ',', '.') }} VNĐ</strong> 
            <br>
            <em>(Bằng chữ: <strong>{{ $booking_deposit_words ?? $booking_deposit_amount_words ?? '' }}</strong>)</em>.
            <br>
            - Hạn chót giữ chỗ độc quyền đến ngày: <strong>{{ $reservation_expires_at ?? 'Theo thỏa thuận' }}</strong>.
            <br>
            - Khi hợp đồng thuê chính thức được ký kết, toàn bộ số tiền cọc giữ chỗ này sẽ được tự động cấn trừ vào Tiền đặt cọc bảo đảm thực hiện hợp đồng nêu tại khoản 4.2 dưới đây.
        </div>
        <div class="clause-p">
            4.2. Tiền đặt cọc bảo đảm thực hiện hợp đồng chính thức là: <strong>{{ number_format($deposit_amount, 0, ',', '.') }} VNĐ</strong> <em>(Bằng chữ: {{ $deposit_amount_words }})</em>.
            <br>
            Phần tiền cọc bổ sung Bên B cần thanh toán khi ký hợp đồng chính thức: <strong>{{ number_format(max(0, $deposit_amount - $booking_deposit_amount), 0, ',', '.') }} VNĐ</strong>.
        </div>
        @else
        <div class="clause-p">
            4.1. Bên B có trách nhiệm đặt cọc cho Bên A số tiền bảo đảm thực hiện hợp đồng là: <strong>{{ number_format($deposit_amount, 0, ',', '.') }} VNĐ</strong>.
            <br>
            <em>(Bằng chữ: <strong>{{ $deposit_amount_words }}</strong>).</em>
        </div>
        @endif
        <div class="clause-p">
            4.2. Số tiền đặt cọc này không được tính lãi suất và sẽ được Bên A hoàn trả lại 100% cho Bên B trong vòng 15 (mười lăm) ngày làm việc kể từ ngày hai bên hoàn tất thủ tục thanh lý hợp đồng, bàn giao lại mặt bằng và Bên B đã thanh toán đầy đủ toàn bộ các khoản công nợ cước phí phát sinh.
        </div>

        <!-- ĐIỀU 5 -->
        <div class="section-title">ĐIỀU 5: QUYỀN VÀ NGHĨA VỤ CỦA CÁC BÊN</div>
        <div class="clause-p">
            5.1. <strong>Quyền và nghĩa vụ của Bên A:</strong>
            <br>
            - Bàn giao mặt bằng thuê đúng thời hạn và hiện trạng kỹ thuật cam kết.
            <br>
            - Đảm bảo an ninh trật tự, hệ thống phòng cháy chữa cháy, hệ thống thang máy vận hành liên tục 24/7.
            <br>
            - Xuất hóa đơn tài chính hợp pháp cho Bên B sau khi nhận đủ tiền thanh toán.
        </div>
        <div class="clause-p">
            5.2. <strong>Quyền và nghĩa vụ của Bên B:</strong>
            <br>
            - Sử dụng diện tích thuê đúng mục đích, chấp hành nghiêm ngặt nội quy tòa nhà và quy định PCCC.
            <br>
            - Thanh toán tiền thuê, tiền cọc và các chi phí điện, nước đúng thời hạn thỏa thuận.
            <br>
            - Không được tự ý chuyển nhượng hoặc cho bên thứ ba thuê lại mặt bằng nếu chưa có sự đồng ý bằng văn bản của Bên A.
        </div>

        <!-- ĐIỀU 6 -->
        <div class="section-title">ĐIỀU 6: ĐIỀU KHOẢN THI HÀNH & HIỆU LỰC</div>
        <div class="clause-p">
            6.1. Hợp đồng này có hiệu lực chính thức kể từ ngày ký và ràng buộc pháp lý đầy đủ đối với cả hai bên.
        </div>
        <div class="clause-p">
            6.2. Mọi sửa đổi, bổ sung điều khoản hợp đồng phải được hai bên thống nhất và lập thành Phụ lục hợp đồng bằng văn bản mới có giá trị pháp lý.
        </div>
        <div class="clause-p">
            6.3. Hợp đồng này được lập thành 04 (bốn) bản gốc bằng tiếng Việt có giá trị pháp lý ngang nhau. Bên A giữ 02 (hai) bản, Bên B giữ 02 (hai) bản để làm căn cứ thực hiện.
        </div>

        <!-- KHỐI CHỮ KÝ HAI BÊN -->
        <div class="signature-section">
            <div class="sign-box">
                <div class="sign-role">ĐẠI DIỆN BÊN A</div>
                <div class="sign-note">(Ký tên, ghi rõ họ tên và đóng dấu)</div>
                <div class="sign-name">{{ $party_a['representative'] }}</div>
            </div>

            <div class="sign-box">
                <div class="sign-role">ĐẠI DIỆN BÊN B</div>
                <div class="sign-note">(Ký tên, ghi rõ họ tên và đóng dấu)</div>
                <div class="sign-name">{{ $party_b['representative'] }}</div>
            </div>
        </div>

    </div>

    <script>
        // Hàm kích hoạt lệnh in chuẩn của trình duyệt (Xuất PDF vector native)
        function triggerPrint() {
            window.print();
        }
    </script>
</body>
</html>
