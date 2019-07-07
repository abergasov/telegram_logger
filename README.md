# telegram_logger
PHP logger for site

## Config documentation
| Key          | Type      | Description |
| ------------ |:----------| :-----------|
| token        | String    | bot token   |
| chats        | Array     | list of chats to send message. See example.php |
| trace_dir    | String    | Directory where should put trace log file. Must be writable. For example /home/admin/web/site/public_html/trace/logs |
| decorate_url | String    | Url for access to file via browser. For example "https://example.com/trace/logs" |
| logs         | Array     | path to acess log and error logs. Must be readable. See example.php for details |
