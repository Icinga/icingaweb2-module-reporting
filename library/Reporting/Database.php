<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Application\Config;
use Icinga\Data\ResourceFactory;
use ipl\Sql;
use PDO;

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

    protected function listTimeframes()
    {
        $select = (new Sql\Select())
            ->from('timeframe')
            ->columns(['id', 'name']);

        $timeframes = [];

        foreach ($this->getDb()->select($select) as $row) {
            $timeframes[$row->id] = $row->name;
        }

        return $timeframes;
    }

    protected function listTemplates()
    {
        $select = (new Sql\Select())
            ->from('template')
            ->columns(['id', 'name']);

        $templates = [];

        foreach ($this->getDb()->select($select) as $row) {
            $templates[$row->id] = $row->name;
        }

        return $templates;
    }
}
