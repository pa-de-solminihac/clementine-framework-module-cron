clementine-framework-module-cron
================================

logs each call of cron/action into database, with start and end date

ignore successive calls by default if previous task is still running, and triggers a notice (you will get it by mail if you enabled it)

you can force the call not to be ignored with force=1
