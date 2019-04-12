# <a id="Installation"></a>Installation

## Requirements

* Icinga Web 2 (&gt;= 2.6)
* PHP (&gt;= 5.6, preferably 7.x)
* MySQL or MariaDB
* Icinga Web 2 modules:
  * [reactbundle](https://github.com/Icinga/icingaweb2-module-reactbundle) (>= 0.4)
  * [Icinga PHP Library (ipl)](https://github.com/Icinga/icingaweb2-module-ipl) (>= 0.1)
  * [pdfexport](https://github.com/Icinga/icingaweb2-module-pdfexport) (>= 0.9)

## Database Setup

The module needs a MySQL/MariaDB database with the schema that's provided in the `etc/schema/mysql.sql` file.

Note that if you're using a version of MySQL < 5.7 or MariaDB < 10.2, the following server options must be set:

```
innodb_file_format=barracuda
innodb_file_per_table=1
innodb_large_prefix=1
```

Example command for creating the MySQL/MariaDB database. Please change the password:

```
CREATE DATABASE reporting;
GRANT SELECT, INSERT, UPDATE, DELETE, DROP, CREATE VIEW, INDEX, EXECUTE ON reporting.* TO reporting@localhost IDENTIFIED BY 'secret';
```

After, you can import the schema using the following command:

```
mysql -p -u root reporting < schema/mysql.sql
```

## Installation

1. Just drop this module to a `reporting` subfolder in your Icinga Web 2 module path.

2. Log in with a privileged user in Icinga Web 2 and enable the module in `Configuration -> Modules -> reporting`.
Or use the `icingacli` and run `icingacli module enable reporting`.

3. Once you've set up the database, create a new Icinga Web 2 resource for it using the
`Configuration -> Application -> Resources` menu.

4. The next step involves telling the Reporting module which database resource to use. This can be done in
`Configuration -> Modules -> reporting -> Backend`.

This concludes the installation. You should now be able create reports.

## Scheduler Daemon

There is a daemon for generating and distributing reports on a schedule if configured:

```
icingacli reporting schedule run
```

This command schedules the execution of all applicable reports.

You may configure this command as `systemd` service. Just copy the example service definition from
`config/systemd/icinga-reporting.service` to `/etc/systemd/system/icinga-reporting.service` and enable it afterwards:

```
systemctl enable icinga-reporting.service
```
