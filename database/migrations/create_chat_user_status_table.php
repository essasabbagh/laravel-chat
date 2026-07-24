<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_user_status', function (Blueprint $table) {
            $table->id();
            $table->morphs('user');
            $table->string('status')->default('offline'); // 'online' | 'away' | 'offline'
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_type', 'user_id'], 'chat_user_status_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_user_status');
    }
};
