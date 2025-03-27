# Upgrading Icinga Reporting <a id="upgrading"></a>

!!! info

    If you have Icinga Web v2.12 or newer installed, you can perform database migrations in the UI.

<!-- {% if not icingaDocs %} -->

**Note:** If you haven't installed this module from packages, then please adapt the database schema
path to the correct installation path.

<!-- {% endif %} -->

## Upgrading to Version 1.0.3

Icinga Reporting version 1.0.3 requires a schema update for the database.
It fixes the `end` time of the preconfigured `Current Week` timeframe.

You may use the following command to apply the database schema upgrade file:

**MySQL:**

```
# mysql -u root -p reporting < /usr/share/icingaweb2/modules/reporting/schema/mysql-upgrades/1.0.3.sql
```

**PostgreSQL:**

```
# psql -U postgres -d reporting -f /usr/share/icingaweb2/modules/reporting/schema/pgsql-upgrades/1.0.3.sql
```

## Upgrading to Version 1.0.0

Icinga Reporting version 1.0.0 requires a schema update for the database.

> **Note**
>
> If you're not using Icinga Web migration automation, you may need to [populate](https://dev.mysql.com/doc/refman/8.0/en/time-zone-support.html#time-zone-installation)
> all the system named time zone information into your MSQL/MariaDB server. Otherwise, the migration may not succeed.

You may use the following command to apply the database schema upgrade file:

```
# mysql -u root -p reporting < /usr/share/icingaweb2/modules/reporting/schema/mysql-upgrades/1.0.0.sql
```

## Upgrading to Version 0.10.0

Icinga Reporting version 0.10.0 requires a schema update for the database.
A new table `template`, linked to table `report`, has been introduced.
Please find the upgrade script in **schema/mysql-upgrades**.

You may use the following command to apply the database schema upgrade file:

```
# mysql -u root -p reporting < /usr/share/icingaweb2/modules/reporting/schema/mysql-upgrades/0.10.0.sql
```

## Upgrading to Version 0.9.1

Icinga Reporting version 0.9.1 requires a schema update for the database.
The schema has been adjusted so that it is no longer necessary to adjust server settings
if you're using a version of MySQL < 5.7 or MariaDB < 10.2.
Further, the start dates for the provided time frames **Last Year** and **Current Year** have been fixed.
Please find the upgrade script in **schema/mysql-migrations**.

You may use the following command to apply the database schema upgrade file:

```
# mysql -u root -p reporting < /usr/share/icingaweb2/modules/reporting/schema/mysql-upgrades/0.9.1.sql
```
