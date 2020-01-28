<?php
require __DIR__ . '/vendor/autoload.php';

use Tlogger\TelegramLogger;

class MyUberClass {

    private $telegramLogger = null;
    const INFO_CHAT = 0;
    const ERROR_CHAT = 1;
    const CONTACT_CHAT = 2;

    public function __construct() {
        $this->telegramLogger = new TelegramLogger([
            'token' => SET_YOUR_TOKEN_HERE,
            'chats' => [
                self::INFO_CHAT => -199024103,
                self::ERROR_CHAT => -293646246,
                self::CONTACT_CHAT => -305488244,
            ],
            'trace_dir' => __DIR__ . '/trace/logs',
            'decorate_url' => 'https://example.com/trace/logs',
            'logs' => [
                'access_log' => '/home/admin/web/my_site/logs/access_log.log',
                'error_log' => '/home/admin/web/my_site/logs/error_log.log',
            ]
        ]);
        $this->telegramLogger->addSlackConfig([
            'token' => SET_YOUR_SLACK_TOKEN_HERE,
            'channels' => [
                self::INFO_CHAT => 'GF4UTEHGB',
                self::ERROR_CHAT => 'GF4UTEHGB',
                self::CONTACT_CHAT => 'GF4UTEHGB',
            ],
        ]);
    }

    public function sentInfoMessage (...$messageData) {
        $result = $this->telegramLogger->sendMessage(self::INFO_CHAT, ...$messageData);
        if ($result) {
            echo 'Info message was send' . PHP_EOL;
        } else {
            echo 'Troubles in send messages' . PHP_EOL;
        }
    }

    public function sentErrorMessage (...$messageData) {
        $result = $this->telegramLogger->sendMessage(self::ERROR_CHAT, ...$messageData);
        if ($result) {
            echo 'Error message was send' . PHP_EOL;
        } else {
            echo 'Troubles in send messages' . PHP_EOL;
        }
    }

    public function sentContactMessage (...$messageData) {
        $result = $this->telegramLogger->sendMessage(self::CONTACT_CHAT, ...$messageData);
        if ($result) {
            echo 'Contact message was send' . PHP_EOL;
        } else {
            echo 'Troubles in send messages' . PHP_EOL;
        }
    }
}

$testObj = new MyUberClass();
$testObj->sentContactMessage('Hello, I need help', 'Additional info 1', 'Additional info 2', 'Additional info 3');
$testObj->sentInfoMessage('New user registered', 'Additional info 4', 'Additional info 5');
try {
    throw new RuntimeException('Something went wrong in this world');
} catch (Throwable $t) {
    $testObj->sentErrorMessage('Exception in script','Additional info 6', 'Additional info 7', $t);
}
