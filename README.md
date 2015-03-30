Clementine Framework : module CRON
===

* Logs each call of cron/action into database, with start and end date

* By default, if previous same task is still running, triggers a notice (you will get it by mail if you have enabled it - and you should have)

* Self-check : sends a list of tasks that seems to take more than `[clementine_cron]warning_if_longer_than` seconds

Configuration
---

```ini
[clementine_cron]
allowed_ip= ; 127.0.0.1 for example
memory_limit=-1
max_execution_time=0
ignore_user_abort=1
warning_if_longer_than=86400
```

Best practices
--------------

***Adding cron tasks on your server***

Add your cron tasks this way, in order to be warned by the system if something fails and was not caught by Clementine :
```bash
cd /path/to/site/root/dir && /path/to/php5 index.php "http://www.domain.com" "cron/task" || echo "PHP return code was $?" | mail -s "Clementine CRON failed : www.domain.com/cron/task" email@domain.com
```

***Monitoring cron tasks***

Add the special __cron/selfcheck__ task to your server, in order to get reports of failed cron tasks :
```bash
cd /path/to/site/root/dir && /path/to/php5 index.php "http://www.domain.com" "cron/selfcheck" || echo "PHP return code was $?" | mail -s "Clementine CRON failed : www.domain.com/cron/selfcheck" email@domain.com
```

***Tips***

* You can force the call not to be ignored by passing the parameter `force=1` :

```bash
# dont ignore consecutive calls
cd /path/to/site/root/dir && /path/to/php5 index.php "http://www.domain.com" "cron/task" "force=1" || echo "PHP return code was $?" | mail -s "Clementine CRON failed : www.domain.com/cron/task" email@domain.com
```
