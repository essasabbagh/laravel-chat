<?php

namespace Essasabbagh\LaravelChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Block extends Model
{
    protected $table = 'chat_blocks';

    protected $fillable = [
        'blocker_type',
        'blocker_id',
        'blocked_type',
        'blocked_id',
    ];

    public function blocker(): MorphTo
    {
        return $this->morphTo();
    }

    public function blocked(): MorphTo
    {
        return $this->morphTo();
    }
}
