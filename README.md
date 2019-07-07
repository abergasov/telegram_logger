# telegram_logger
PHP logger for web site/app

## Example messages
![telegram_logger](https://github.com/abergasov/telegram_logger/blob/master/images/img_1.png?raw=true)
![telegram_logger](https://github.com/abergasov/telegram_logger/blob/master/images/img_2.png?raw=true)

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