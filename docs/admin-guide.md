# Admin Guide

The package ships with optional admin endpoints and an `AdminChatService` for moderation and management of conversations, users, and content.

## Configuration

All admin features are disabled by default and must be enabled in `config/chat.php`:

```php
'admin' => [
    'allow_block'         => false,
    'allow_force_offline' => false,
    'allow_delete'        => false,
    'allow_status_change' => false,
],
```

## Service Usage

The `AdminChatService` class is bound in the container under `Essasabbagh\LaravelChat\Services\AdminChatService`.

### Block / Unblock

```php
use Essasabbagh\LaravelChat\Services\AdminChatService;

$admin = app(AdminChatService::class);

// Block a participant
$admin->blockParticipant('App\Models\User', 1, 'App\Models\User', 2);

// Unblock
$admin->unblockParticipant('App\Models\User', 1, 'App\Models\User', 2);
```

### Force Offline

```php
$admin->forceOffline('App\Models\User', 1);
```

### Delete Content

```php
$admin->deleteMessage($message);       // Permanently delete a message
$admin->deleteConversation($conv);     // Permanently delete a conversation
```

### Change Status

```php
$admin->changeUserStatus('App\Models\User', 1, 'away');
// Valid statuses: online, away, offline
```

## API Endpoints

All admin endpoints are prefixed with `/api/chat/admin/` and require the `api` middleware.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/admin/block` | Block a participant |
| POST | `/admin/unblock` | Unblock a participant |
| POST | `/admin/users/force-offline` | Force a user offline |
| DELETE | `/admin/conversations/{id}` | Delete a conversation |
| DELETE | `/admin/conversations/{id}/messages/{id}` | Delete a message |
| POST | `/admin/users/status` | Change a user's status |

All endpoints return `403` when the corresponding `allow_*` config flag is `false`.

## Planned: Filament Module

A dedicated Filament PHP admin panel module is planned for a future release. The endpoints above provide the programmatic API layer that the Filament module will consume.
