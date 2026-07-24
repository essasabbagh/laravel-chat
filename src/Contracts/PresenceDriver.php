<?php

namespace Essasabbagh\LaravelChat\Contracts;

interface PresenceDriver
{
    public function online(string|int $userId, string $userType): void;

    public function away(string|int $userId, string $userType): void;

    public function offline(string|int $userId, string $userType): void;

    public function status(string|int $userId, string $userType): ?string;

    public function allOnline(): array;
}
