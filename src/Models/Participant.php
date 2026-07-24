<?php

namespace Essasabbagh\LaravelChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Participant extends Model
{
    protected $table = 'chat_participants';

    protected $fillable = [
        'conversation_id',
        'participantable_type',
        'participantable_id',
        'role',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'role' => 'string',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function participantable(): MorphTo
    {
        return $this->morphTo();
    }
}
