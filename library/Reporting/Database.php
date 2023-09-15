<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Application\Config;
use Icinga\Data\ResourceFactory;
use ipl\Sql;
use PDO;
use stdClass;

trait Database
{
    protected function getDb(): RetryConnection
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
    protected function listTimeframes(): array
    {
        return $this->list(
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
    protected function listTemplates(): array
    {
        return $this->list(
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
    private function list(Sql\Select $select): array
    {
        $result = [];
        /** @var stdClass $row */
        foreach ($this->getDb()->select($select) as $row) {
            /** @var int $id */
            $id = $row->id;
            /** @var string $name */
            $name = $row->name;

            $result[$id] = $name;
        }

        return $result;
    }
}
