<?php

namespace Essasabbagh\LaravelChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    protected $table = 'chat_reactions';

    protected $fillable = [
        'message_id',
        'reactor_type',
        'reactor_id',
        'emoji',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function reactor(): MorphTo
    {
        return $this->morphTo();
    }
}
