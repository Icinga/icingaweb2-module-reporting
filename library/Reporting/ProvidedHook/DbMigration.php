<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\ProvidedHook;

use Icinga\Application\Hook\DbMigrationHook;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model\Schema;
use ipl\Orm\Query;
use ipl\Sql\Connection;

class DbMigration extends DbMigrationHook
{
    public function getName(): string
    {
        return $this->translate('Icinga Reporting');
    }

    public function providedDescriptions(): array
    {
        return [
            '0.9.1' => $this->translate(
                'Modifies all columns that uses current_timestamp to unix_timestamp and alters the database'
                . ' engine of some tables.'
            ),
            '0.10.0' => $this->translate('Creates the template table and adjusts some column types'),
            '1.0.0'  => $this->translate('Migrates all your configured report schedules to the new config.'),
            '1.0.3'  => $this->translate('Fix the `end` time of preconfigured `Current Week` timeframe.'),
        ];
    }

    protected function getSchemaQuery(): Query
    {
        return Schema::on($this->getDb());
    }

    public function getDb(): Connection
    {
        return Database::get();
    }

    public function getVersion(): string
    {
        if ($this->version === null) {
            $conn = $this->getDb();
            $schema = $this->getSchemaQuery()
                ->columns(['version', 'success'])
                ->orderBy('id', SORT_DESC)
                ->limit(2);

            if (static::tableExists($conn, $schema->getModel()->getTableName())) {
                /** @var Schema $version */
                foreach ($schema as $version) {
                    if ($version->success) {
                        $this->version = $version->version;

                        break;
                    }
                }

                if (! $this->version) {
                    // Schema version table exist, but the user has probably deleted the entry!
                    $this->version = '1.0.0';
                }
            } elseif (static::tableExists($conn, 'template')) {
                // We have added Postgres support and the template table with 0.10.0.
                // So, use this as the last (migrated) version.
                $this->version = '0.10.0';
            } elseif (static::getColumnType($conn, 'timeframe', 'name') === 'varchar(128)') {
                // Upgrade script 0.9.1 alters the timeframe.name column from `varchar(255)` -> `varchar(128)`.
                // Therefore, we can safely use this as the last migrated version.
                $this->version = '0.9.1';
            } else {
                // Use the initial version as the last migrated schema version!
                $this->version = '0.9.0';
            }
        }

        return $this->version;
    }
}
