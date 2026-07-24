<?php

namespace Essasabbagh\LaravelChat\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestCustomer extends Model
{
    use HasFactory;

    protected $table = 'test_customers';

    protected $guarded = [];
}
