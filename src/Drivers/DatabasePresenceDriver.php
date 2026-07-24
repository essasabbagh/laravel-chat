<?php

namespace Essasabbagh\LaravelChat\Drivers;

use Essasabbagh\LaravelChat\Contracts\PresenceDriver;
use Essasabbagh\LaravelChat\Events\UserPresenceChanged;
use Essasabbagh\LaravelChat\Models\UserStatus;

class DatabasePresenceDriver implements PresenceDriver
{
    public function online(string|int $userId, string $userType): void
    {
        UserStatus::updateOrCreate(
            ['user_type' => $userType, 'user_id' => $userId],
            ['status' => 'online', 'last_seen_at' => now()],
        );

        UserPresenceChanged::dispatch($userType, $userId, 'online');
    }

    public function away(string|int $userId, string $userType): void
    {
        UserStatus::updateOrCreate(
            ['user_type' => $userType, 'user_id' => $userId],
            ['status' => 'away', 'last_seen_at' => now()],
        );

        UserPresenceChanged::dispatch($userType, $userId, 'away');
    }

    public function offline(string|int $userId, string $userType): void
    {
        UserStatus::updateOrCreate(
            ['user_type' => $userType, 'user_id' => $userId],
            ['status' => 'offline', 'last_seen_at' => now()],
        );

        UserPresenceChanged::dispatch($userType, $userId, 'offline');
    }

    public function status(string|int $userId, string $userType): ?string
    {
        return UserStatus::where('user_type', $userType)
            ->where('user_id', $userId)
            ->value('status');
    }

    public function allOnline(): array
    {
        return UserStatus::whereIn('status', ['online', 'away'])
            ->get()
            ->toArray();
    }
}
