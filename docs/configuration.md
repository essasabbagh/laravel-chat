# Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="Essasabbagh\LaravelChat\ChatServiceProvider" --tag="config"
```

## Available Options

### `participant_models`
Map of participant types to Eloquent models