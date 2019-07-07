<?php
require __DIR__ . '/vendor/autoload.php';

use Tlogger\TelegramLogger;

$logger = new TelegramLogger([
    'token' => '1234',
    'chats' => [
        -199024103, -293646246, 305488244
    ],
    'trace_dir' => __DIR__ . 'trace/logs',
    'decorate_url' => 'https://example.com/trace/logs',
    'logs' => [
        'access_log' => '/home/admin/web/my_site/logs/access_log.log',
        'error_log' => '/home/admin/web/my_site/logs/error_log.log',
    ]
]);
