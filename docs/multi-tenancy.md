# Multi-Tenancy

Laravel Chat supports multi-tenancy through the TenantResolver contract.

## Custom Tenant Resolver

Implement Essasabbagh\LaravelChat\Contracts\TenantResolver and bind it:

```php
use Essasabbagh\LaravelChat\Contracts\TenantResolver;

app()->bind(TenantResolver::class, fn () => new class implements TenantResolv
