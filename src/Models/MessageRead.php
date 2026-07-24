<?php

namespace Essasabbagh\LaravelChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MessageRead extends Model
{
    protected $table = 'chat_message_reads';

    protected $fillable = [
        'message_id',
        'participantable_type',
        'participantable_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function participantable(): MorphTo
    {
        return $this->morphTo();
    }
}
