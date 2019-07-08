# telegram_logger
PHP logger for web site/app

## Example messages
![telegram_logger](https://github.com/abergasov/telegram_logger/blob/master/images/img_1.png?raw=true)
![telegram_logger](https://github.com/abergasov/telegram_logger/blob/master/images/img_2.png?raw=true)

## Instalation
> composer require abergasov/telegram_logger

## Usage
```
const INFO_CHAT = 0;
const ERROR_CHAT = 1;
const CONTACT_CHAT = 2;

$logger = new TelegramLogger([
    'token' => SET_YOUR_TOKEN_HERE,
    'chats' => [
        INFO_CHAT => -199024103,
        ERROR_CHAT => -293646246,
        CONTACT_CHAT => -305488244,
        ... add more or less if need
    ],
    'trace_dir' => __DIR__ . '/trace/logs',
    'decorate_url' => 'https://example.com/trace/logs',
    'logs' => [
        'access_log' => '/home/admin/web/my_site/logs/access_log.log',
        'error_log' => '/home/admin/web/my_site/logs/error_log.log',
    ]
]);

$result = $logger->sendMessage(INFO_CHAT, 'Hello, I need help', 'Additional info 1', 'Additional info 2', 'Additional info 3');
echo ($result ? 'Info message was send' : 'Troubles in send messages') . PHP_EOL;

try {
    throw new RuntimeException('Something went wrong in this world');
} catch (Throwable $t) {
    $logger->sendMessage(ERROR_CHAT, 'Exception in script', $t);
}
```

## Config documentation
| Key          | Type      | Description |
| ------------ |:----------| :-----------|
| token        | String    | bot token   |
| chats        | Array     | list of chats to send message. See example.php |
| trace_dir    | String/Boolean    | By default false Directory where should put trace log file. Must be writable. For example /home/admin/web/site/public_html/trace/logs |
| decorate_url | String    | Empty by default. Url for access to file via browser. For example "https://example.com/trace/logs" |
| logs         | Array     | Empty by default. path to acess log and error logs. Must be readable. See example.php for details |

## Creating telegram bot tutorial
- Find BotFather.
- Send /newbot.
- Set up name and bot-name for your bot.
- Get token and add it to your .env file.
- Find your bot (BotFather already generate link to it in last message).
- Send one or few messages to him.
- Open next url https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates and find chat_id.
- find @myidbot and send him /getid for personal chat
- find @myidbot add him to group and send him /getgroupid@myidbot for chat id

![telegram_logger](https://github.com/abergasov/telegram_logger/blob/master/images/img_3.png?raw=true)