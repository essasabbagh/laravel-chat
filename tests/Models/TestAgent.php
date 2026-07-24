<?php

namespace Essasabbagh\LaravelChat\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAgent extends Model
{
    use HasFactory;

    protected $table = 'test_agents';

    protected $guarded = [];
}
