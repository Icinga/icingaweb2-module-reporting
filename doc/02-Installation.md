# Installing Icinga Reporting

Please see the Icinga Web documentation on
[how to install modules](https://icinga.com/docs/icinga-web-2/latest/doc/08-Modules/#installation) from source.
Make sure you use `reporting` as the module name. The following requirements must also be met.

## Requirements

* PHP (≥7.2)
* MySQL or PostgreSQL PDO PHP libraries
* The following PHP modules must be installed: `mbstring`
* [Icinga Web](https://github.com/Icinga/icingaweb2) (≥2.9)
* [Icinga PHP Library (ipl)](https://github.com/Icinga/icinga-php-library) (≥0.13.0)
* [Icinga PHP Thirdparty](https://github.com/Icinga/icinga-php-thirdparty) (≥0.12.0)

## Setting up the Database

### Setting up a MySQL or MariaDB Database

The module needs a MySQL/MariaDB database with the schema that's provided in the `schema/mysql.schema.sql` file.

You can use the following sample command for creating the MySQL/MariaDB database. Please change the password:

```
CREATE DATABASE reporting;
GRANT SELECT, INSERT, UPDATE, DELETE, DROP, CREATE, ALTER, CREATE VIEW, INDEX, EXECUTE ON reporting.* TO reporting@localhost IDENTIFIED BY 'secret';
```

After, you can import the schema using the following command:

```
mysql -p -u root reporting < /usr/share/icingaweb2/modules/reporting/schema/mysql.schema.sql
```

## Setting up a PostgreSQL Database

The module needs a PostgreSQL database with the schema that's provided in the `schema/pgsql.schema.sql` file.

You can use the following sample command for creating the PostgreSQL database. Please change the password:

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
psql -U reporting reporting -a -f /usr/share/icingaweb2/modules/reporting/schema/pgsql.schema.sql
```

This concludes the installation. Now continue with the [configuration](03-Configuration.md).
