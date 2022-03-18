# Configuration

1. [Backend](#backend)
2. [Mail](#mail)
3. [Permissions](#permissions)

## Backend

If not already done during the installation of Icinga Reporting, setup the reporting database backend now.

Create a new [Icinga Web 2 resource](https://icinga.com/docs/icingaweb2/latest/doc/04-Resources/#database)
for [Icinga Reporting's database](https://icinga.com/docs/icinga-reporting/latest/doc/02-Installation/#database-setup)
using the `Configuration -> Application -> Resources` menu.

Then tell Icinga Reporting which database resource to use. This can be done in
`Configuration -> Modules -> reporting -> Backend`. If you've used `reporting`
as name for the resource, this is optional.

## Mail

At `Configuration -> Modules -> reporting -> Mail` you can configure the address
that is used as the sender's address (From) in E-mails.

## Permissions

There are four permissions that can be used to control what can be managed by whom.

Permission           | Applies to
---------------------|----------------
reporting/reports    | Reports (create, edit, delete)
reporting/schedules  | Schedules (create, edit, delete)
reporting/templates  | Templates (create, edit, delete)
reporting/timeframes | Timeframes (create, edit, delete)
