<?php

return [
    'participant_models' => [
        'user' => 'App\Models\User',
    ],

    'tenancy' => [
        'enabled' => false,
        'resolver' => null,
    ],

    'presence' => [
        'driver' => 'database',
    ],

    'features' => [
        'attachments' => true,
        'reactions' => true,
        'voice_messages' => true,
        'groups' => true,
    ],

    'storage' => [
        'disk' => 'local',
        'max_file_size' => 10240,
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'mp3', 'mp4', 'ogg'],
    ],

    'broadcasting' => [
        'enabled' => true,
    ],

    'groups' => [
        'who_can_create' => 'any', // 'any' | 'admin_role'
    ],

    'admin' => [
        'allow_block' => true,
        'allow_force_offline' => true,
        'allow_delete' => true,
        'allow_status_change' => true,
    ],
];
