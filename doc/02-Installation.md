<!-- {% if index %} -->
# Installing Icinga Reporting

The recommended way to install Icinga Reporting and its dependencies is to use prebuilt packages for all supported
platforms from our official release repository. Please note that [Icinga Web](https://icinga.com/docs/icinga-web) is
required and if it is not already set up, it is best to do this first.

To upgrade an existing Icinga Reporting installation to a newer version, see the [Upgrading](80-Upgrading.md) documentation
for the necessary steps.
<!-- {% else %} -->
<!-- {% if not icingaDocs %} -->

## Installing the Package

If the [repository](https://packages.icinga.com) is not configured yet, please add it first.
Then use your distribution's package manager to install the `icinga-reporting` package
or install [from source](02-Installation.md.d/From-Source.md).
<!-- {% endif %} -->

## Setting up the Database

### Setting up a MySQL or MariaDB Database

The module needs a MySQL/MariaDB database with the schema that's provided in the `/usr/share/icingaweb2/modules/reporting/schema/mysql.schema.sql` file.
<!-- {% if not icingaDocs %} -->

**Note:** If you haven't installed this module from packages, then please adapt the schema path to the correct installation path.

<!-- {% endif %} -->

You can use the following sample command for creating the MySQL/MariaDB database. Please change the password:

```
CREATE DATABASE reporting;
GRANT SELECT, INSERT, UPDATE, DELETE, DROP, CREATE, CREATE VIEW, INDEX, EXECUTE ON reporting.* TO reporting@localhost IDENTIFIED BY 'secret';
```

After, you can import the schema using the following command:

```
mysql -p -u root reporting < /usr/share/icingaweb2/modules/reporting/schema/mysql.schema.sql
```

## Setting up a PostgreSQL Database

The module needs a PostgreSQL database with the schema that's provided in the `/usr/share/icingaweb2/modules/reporting/schema/pgsql.schema.sql` file.
<!-- {% if not icingaDocs %} -->

**Note:** If you haven't installed this module from packages, then please adapt the schema path to the correct installation path.

<!-- {% endif %} -->

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
psql -U reporting reporting -a -f /usr/share/icingaweb2/modules/reporting/pgsql.schema.sql
```

This concludes the installation. Now continue with the [configuration](03-Configuration.md).
<!-- {% endif %} --><!-- {# end else if index #} -->
