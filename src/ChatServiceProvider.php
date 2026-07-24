<?php

namespace Essasabbagh\LaravelChat;

use Essasabbagh\LaravelChat\Contracts\PresenceDriver;
use Essasabbagh\LaravelChat\Contracts\TenantResolver;
use Essasabbagh\LaravelChat\Drivers\DatabasePresenceDriver;
use Essasabbagh\LaravelChat\Resolvers\NullTenantResolver;
use Essasabbagh\LaravelChat\View\Components\ChatBubble;
use Essasabbagh\LaravelChat\View\Components\ChatWindow;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChatServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-chat')
            ->hasConfigFile('chat')
            ->hasRoute('api')
            ->hasRoute('channels')
            ->hasViews()
            ->hasMigrations([
                'create_chat_conversations_table',
                'create_chat_participants_table',
                'create_chat_messages_table',
                'create_chat_attachments_table',
                'create_chat_reactions_table',
                'create_chat_message_reads_table',
                'create_chat_user_status_table',
                'create_chat_blocks_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->bind(TenantResolver::class, NullTenantResolver::class);
        $this->app->bind(PresenceDriver::class, DatabasePresenceDriver::class);
    }

    public function bootingPackage(): void
    {
        $this->loadViewComponentsAs('chat', [
            ChatBubble::class,
            ChatWindow::class,
        ]);
    }
}
