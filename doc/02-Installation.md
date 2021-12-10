# Installation <a id="installation"></a>

## Requirements <a id="installation-requirements">

* PHP (>= 7.2)
* MySQL / MariaDB or PostgreSQL
* [Icinga Web 2](https://github.com/Icinga/icingaweb2) (>= 2.7)
* [Icinga PDF Export](https://github.com/Icinga/icingaweb2-module-pdfexport) (>= 0.10)

If your Icinga Web 2 is **not** v2.9+, the following modules are also required:

* [reactbundle](https://github.com/Icinga/icingaweb2-module-reactbundle) (0.9.0)
* [Icinga PHP Library (ipl)](https://github.com/Icinga/icingaweb2-module-ipl) (0.5.0)

## Database Setup <a id="installation-database-setup">

### MySQL / MariaDB

The module needs a MySQL/MariaDB database with the schema that's provided in the `etc/schema/mysql.sql` file.

Example command for creating the MySQL/MariaDB database. Please change the password:

```
CREATE DATABASE reporting;
GRANT SELECT, INSERT, UPDATE, DELETE, DROP, CREATE VIEW, INDEX, EXECUTE ON reporting.* TO reporting@localhost IDENTIFIED BY 'secret';
```

After, you can import the schema using the following command:

```
mysql -p -u root reporting < schema/mysql.sql
```

## PostgreSQL

The module needs a PostgreSQL database with the schema that's provided in the `etc/schema/postgresql.sql` file.

Example command for creating the PostgreSQL database. Please change the password:

```sql
CREATE USER reporting WITH PASSWORD 'secret';
CREATE DATABASE reporting
  WITH OWNER reporting
  ENCODING 'UTF8'
  LC_COLLATE = 'en_US.UTF-8'
  LC_CTYPE = 'en_US.UTF-8';
```

After, you can import the schema using the following command:

```
psql -U reporting reporting -a -f schema/postgresql.sql
```

## Module Installation <a id="installation-module">

1. Just drop this module to a `reporting` subfolder in your Icinga Web 2 module path.

2. Log in with a privileged user in Icinga Web 2 and enable the module in `Configuration -> Modules -> reporting`.
Or use the `icingacli` and run `icingacli module enable reporting`.

3. Once you've set up the database, create a new Icinga Web 2 resource for it using the
`Configuration -> Application -> Resources` menu. Make sure that you set the character set to `utf8mb4`.

4. The next step involves telling the Reporting module which database resource to use. This can be done in
`Configuration -> Modules -> reporting -> Backend`.

This concludes the installation. You should now be able create reports.

## Scheduler Daemon <a id="installation-scheduler-daemon">

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
