<?php

namespace Essasabbagh\LaravelChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserStatus extends Model
{
    protected $table = 'chat_user_status';

    protected $fillable = [
        'user_type',
        'user_id',
        'status',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'status' => 'string',
        ];
    }

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
