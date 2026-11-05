<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thư viện ảnh chụp / Bản scan hợp đồng và các phụ lục pháp lý đính kèm.
     */
    public function up(): void
    {
        Schema::create('contract_images', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('contract_id')->constrained('contracts')->cascadeOnDelete(); // Hợp đồng sở hữu tệp đính kèm.
            $table->string('image_url', 255); // Đường dẫn tệp tin trên Private Storage.
            $table->string('document_type', 40); // Loại tài liệu: SIGNED_CONTRACT_SCAN | APPENDIX | HANDOVER_MINUTES | PAYMENT_RECEIPT | OTHER.
            $table->string('file_name', 255)->nullable(); // Tên gốc của tệp tải lên.
            $table->timestamp('created_at')->useCurrent();
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `contract_images` ADD CONSTRAINT `chk_cimg_doc_type` CHECK (`document_type` IN ('SIGNED_CONTRACT_SCAN', 'APPENDIX', 'HANDOVER_MINUTES', 'PAYMENT_RECEIPT', 'OTHER'))");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE contract_images ADD CONSTRAINT chk_cimg_doc_type CHECK (document_type IN ('SIGNED_CONTRACT_SCAN', 'APPENDIX', 'HANDOVER_MINUTES', 'PAYMENT_RECEIPT', 'OTHER'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_images');
    }
};
