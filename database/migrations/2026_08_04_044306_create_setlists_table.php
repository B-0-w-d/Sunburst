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
        Schema::create('setlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('show_id');
            $table->unsignedBigInteger('song_id');
            $table->unsignedBigInteger('event_id')->nullable(); // Liên kết tới bảng Event khi tạo lịch tập/diễn
            $table->text('description')->nullable(); // Ghi chú riêng cho bài trong show này
            $table->json('target_member_ids')->nullable(); // Mảng ID thành viên tham gia diễn bài này
            $table->timestamps();

            // Foreign keys (tuỳ thuộc vào việc bạn dùng kiểu ID nào: bigint hay string/ObjectId)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setlists');
    }
};
