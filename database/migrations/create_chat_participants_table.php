<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->morphs('participantable');
            $table->string('role')->default('member'); // 'member' | 'admin'
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'participantable_type', 'participantable_id'], 'chat_participants_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
};
