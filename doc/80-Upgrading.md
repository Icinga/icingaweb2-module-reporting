# Upgrading Icinga Reporting <a id="upgrading"></a>

Upgrading Icinga Reporting is straightforward.
Usually the only manual steps involved are schema updates for the database.

## Upgrading to Version 0.10.0

Icinga Reporting version 0.10.0 requires a schema update for the database.
A new table `template`, linked to table `report`, has been introduced.
Please find the upgrade script in **schema/mysql-upgrades**.

You may use the following command to apply the database schema upgrade file:

```
# mysql -u root -p reporting <schema/mysql-upgrades/0.10.0.sql
```

## Upgrading to Version 0.9.1

Icinga Reporting version 0.9.1 requires a schema update for the database.
The schema has been adjusted so that it is no longer necessary to adjust server settings
if you're using a version of MySQL < 5.7 or MariaDB < 10.2.
Further, the start dates for the provided time frames **Last Year** and **Current Year** have been fixed.
Please find the upgrade script in **schema/mysql-migrations**.

You may use the following command to apply the database schema upgrade file:

```
# mysql -u root -p reporting <schema/mysql-upgrades/0.9.1.sql
```
