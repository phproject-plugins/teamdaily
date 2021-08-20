# teamdaily

A specialized dashboard to show the status of individual team members. It shows wins/losses from the previous day in the form of hours logged, tasks closed, and late tasks.

Color coded panels indicate overall success based on points in each category. Typical Success (Green), Warning (Yellow), and Danger (Red) classes are used from bootsrap to quickly identify status.

## Requirements

These reports rely on the MySQL Time Zone tables. If they aren't set up on your database server, you'll get empty data in the reports.

See the [MySQL documentation](https://dev.mysql.com/doc/refman/8.0/en/mysql-tzinfo-to-sql.html) for information on setting up those tables.
