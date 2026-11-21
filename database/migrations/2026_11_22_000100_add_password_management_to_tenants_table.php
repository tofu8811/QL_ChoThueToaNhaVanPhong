<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bổ sung các trường quản lý mật khẩu khởi tạo & bắt buộc đổi mật khẩu cho Tenant Portal.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->dateTime('password_changed_at')->nullable()->after('must_change_password');
            $table->dateTime('initial_password_issued_at')->nullable()->after('password_changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'must_change_password',
                'password_changed_at',
                'initial_password_issued_at',
            ]);
        });
    }
};
