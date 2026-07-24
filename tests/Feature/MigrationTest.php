<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    /** @test */
    public function all_tables_exist_after_migration()
    {
        $tables = [
            'chat_conversations',
            'chat_participants',
            'chat_messages',
            'chat_attachments',
            'chat_reactions',
            'chat_message_reads',
            'chat_user_status',
            'chat_blocks',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table {$table} does not exist"
            );
        }
    }
}
