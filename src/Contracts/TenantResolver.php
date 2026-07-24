<?php

namespace Essasabbagh\LaravelChat\Contracts;

interface TenantResolver
{
    public function resolve(): string|int|null;
}
