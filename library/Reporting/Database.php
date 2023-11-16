<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Application\Config;
use Icinga\Data\ResourceFactory;
use ipl\Sql;
use PDO;
use stdClass;

final class Database
{
    /** @var RetryConnection Database connection */
    private static $instance;

    private function __construct()
    {
    }

    /**
     * Get the database connection
     *
     * @return RetryConnection
     */
    public static function get(): RetryConnection
    {
        if (self::$instance === null) {
            self::$instance = self::getDb();
        }

        return self::$instance;
    }

    private static function getDb(): RetryConnection
    {
        $config = new Sql\Config(
            ResourceFactory::getResourceConfig(
                Config::module('reporting')->get('backend', 'resource', 'reporting')
            )
        );

        $config->options = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ];
        if ($config->db === 'mysql') {
            $config->options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET SESSION SQL_MODE='STRICT_TRANS_TABLES"
                . ",NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
        }

        return new RetryConnection($config);
    }

    /**
     * List all reporting timeframes
     *
     * @return array<int, string>
     */
    public static function listTimeframes(): array
    {
        return self::list(
            (new Sql\Select())
                ->from('timeframe')
                ->columns(['id', 'name'])
        );
    }

    /**
     * List all reporting templates
     *
     * @return array<int, string>
     */
    public static function listTemplates(): array
    {
        return self::list(
            (new Sql\Select())
                ->from('template')
                ->columns(['id', 'name'])
        );
    }

    /**
     * Helper method for list templates and timeframes
     *
     * @param Sql\Select $select
     *
     * @return array<int, string>
     */
    private static function list(Sql\Select $select): array
    {
        $result = [];
        /** @var stdClass $row */
        foreach (self::get()->select($select) as $row) {
            /** @var int $id */
            $id = $row->id;
            /** @var string $name */
            $name = $row->name;

            $result[$id] = $name;
        }

        return $result;
    }
}
