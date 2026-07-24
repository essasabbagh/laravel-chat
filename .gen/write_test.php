#!/usr/bin/env php
<?php
$file = $argv[1];
$lines = [
    '<?php',
    '',
    'namespace Essasabbagh\LaravelChat\Tests\Feature;',
    '',
    'use Essasabbagh\LaravelChat\Models\Conversation;',
    'use Essasabbagh\LaravelChat\Models\Participant;',
    'use Essasabbagh\LaravelChat\Models\Message;',
    'use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;',
    'use Essasabbagh\LaravelChat\Tests\Models\TestAgent;',
    'use Essasabbagh\LaravelChat\Tests\TestCase;',
    '',
    'class ConversationsTest extends TestCase',
    '{',
    '    private TestCustomer $customer;',
    '    private TestAgent $agent;',
    '',
    '    protected function setUp(): void',
    '    {',
    '        parent::setUp();',
    '        $this->customer = TestCustomer::create([\'name\' => \'Alice\']);',
    '        $this->agent = TestAgent::create([\'name\' => \'Bob\']);',
    '    }',
    '}',
];
file_put_contents($file, implode("\n", $lines) . "\n");
echo "OK\n";