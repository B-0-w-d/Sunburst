<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();

            // Thay thế phần morphs gây lỗi bằng cách định nghĩa tường minh cột kiểu dữ liệu
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id'); // Hoặc dùng string('tokenable_id') nếu _id của Member là dạng chuỗi/ObjectId của MongoDB
            $table->index(['tokenable_type', 'tokenable_id']);

            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications'); // Sửa lại đúng tên bảng ở đây
    }
};
