# Troubleshooting <a id="troubleshooting"></a>

## MySQL Import: Key too long <a id="troubleshooting-mysql-schema-key"></a>

MySQL schema import fails like this:

```
ERROR 1071 (42000) at line 1: Specified key was too long; max key length is 767 bytes
```

Ensure to follow the [database setup](02-Installation.md#installation-database-setup)
and enable `innodb_large_prefix` in your database server configuration.
