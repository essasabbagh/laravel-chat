<?php

namespace Essasabbagh\LaravelChat\Resolvers;

use Essasabbagh\LaravelChat\Contracts\TenantResolver;

class NullTenantResolver implements TenantResolver
{
    public function resolve(): string|int|null
    {
        return null;
    }
}
