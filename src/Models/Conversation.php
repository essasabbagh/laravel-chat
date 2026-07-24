<?php

namespace Essasabbagh\LaravelChat\Models;

use Essasabbagh\LaravelChat\Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $type
 * @property string|null $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_conversations';

    protected $fillable = [
        'type',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }

    protected static function newFactory(): ConversationFactory
    {
        return ConversationFactory::new();
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
