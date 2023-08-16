<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Clicommands;

use Icinga\Module\Reporting\Cli\Command;
use Icinga\Module\Reporting\Model;
use InvalidArgumentException;
use ipl\Stdlib\Filter;

class ListCommand extends Command
{
    /**
     * List reports
     *
     * USAGE
     *
     *     icingacli reporting list [OPTIONS]
     *
     * OPTIONS
     *
     * --sort=<id|name|author>
     *     Sort the reports by the given column. Defaults to id.
     *
     * --direction=<asc|desc>
     *     Sort the reports by the specified sort column in ascending or descending order. Defaults to asc.
     *
     * --filter=<name>
     *     Filter the reports by the specified report name. Performs a wildcard search by default.
     *
     * EXAMPLES
     *
     * Sort the reports by name:
     *     icingacli reporting list --sort=name
     *
     * Sort the reports by author in descending order:
     *     icingacli reporting list --sort=author --direction=DESC
     *
     * Filter the reports that contain "Host" in the report name:
     *     icingacli reporting list --filter=Host
     *
     * Filter the reports that begin with "Service":
     *     icingacli reporting list --filter=Service*
     *
     * Filter the reports that end with "SLA":
     *     icingacli reporting list --filter=*SLA
     */
    public function indexAction()
    {
        $sort = strtolower($this->params->get('sort', 'id'));

        if ($sort !== 'id' && $sort !== 'name' && $sort !== 'author') {
            throw new InvalidArgumentException(sprintf('Sorting by %s is not supported', $sort));
        }

        $direction = $this->params->get('direction', 'ASC');

        $reports = Model\Report::on($this->getDb());
        $reports
            ->with(['reportlets'])
            ->orderBy($sort, $direction);

        $filter = $this->params->get('filter');
        if ($filter !== null) {
            if (strpos($filter, '*') === false) {
                $filter = '*' . $filter . '*';
            }
            $reports->filter(Filter::like('name', $filter));
        }

        if ($reports->count() === 0) {
            print $this->translate("No reports found\n");
            exit;
        }

        $dataCallbacks = [
            'ID'     => function ($report) {
                return $report->id;
            },
            'Name'   => function ($report) {
                return $report->name;
            },
            'Author' => function ($report) {
                return $report->author;
            },
            'Type'   => function ($report) {
                return (new $report->reportlets->class())->getName();
            }
        ];

        $this->outputTable($reports, $dataCallbacks);
    }

    protected function outputTable($reports, array $dataCallbacks)
    {
        $columnsAndLengths = [];
        foreach ($dataCallbacks as $key => $_) {
            $columnsAndLengths[$key] = strlen($key);
        }

        $rows = [];
        foreach ($reports as $report) {
            $row = [];
            foreach ($dataCallbacks as $key => $callback) {
                $row[] = $callback($report);
                $columnsAndLengths[$key] = max($columnsAndLengths[$key], mb_strlen($callback($report)));
            }

            $rows[] = $row;
        }

        $format = '|';
        $beautifier = '|';
        foreach ($columnsAndLengths as $length) {
            $headerFormat = " %-" . sprintf('%ss |', $length);
            $format .= $headerFormat;
            $beautifier .= sprintf($headerFormat, str_repeat('-', $length));
        }
        $format .= "\n";
        $beautifier .= "\n";

        printf($format, ...array_keys($columnsAndLengths));
        print $beautifier;

        foreach ($rows as $row) {
            printf($format, ...$row);
        }
    }
}
