<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->morphs('participantable');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['message_id', 'participantable_type', 'participantable_id'], 'chat_message_reads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_reads');
    }
};
