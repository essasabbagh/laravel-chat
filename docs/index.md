# Laravel Chat

Real-time messaging package for Laravel with multi-tenancy, broadcasting, admin moderation, and Blade components.

## Documentation

- [Installation](installation.md) — setup, config, migrations, basic usage
- [Configuration](configuration.md) — all config options
- [Events & Broadcasting](events-and-broadcasting.md) — real-time events setup
- [Multi-Tenancy](multi-tenancy.md) — tenant isolation
- [Admin Guide](admin-guide.md) — moderation API

## Quick Start

```bash
composer require essasabbagh/laravel-chat
php artisan vendor:publish --tag="chat-config"
php artisan migrate
```

## License

MIT