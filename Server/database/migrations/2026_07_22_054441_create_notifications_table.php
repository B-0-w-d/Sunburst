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
        Schema::create('notifications', function (Blueprint $collection) {
            $collection->id();
            $collection->string('type');
            $collection->string('notifiable_type');
            $collection->string('notifiable_id'); // Lưu ID của member dưới dạng chuỗi
            $collection->text('data'); // Lưu nội dung JSON của thông báo
            $collection->timestamp('read_at')->nullable();
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
