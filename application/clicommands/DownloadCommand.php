<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Clicommands;

use Icinga\Exception\NotFoundError;
use Icinga\Module\Pdfexport\ProvidedHook\Pdfexport;
use Icinga\Module\Reporting\Cli\Command;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Report;
use InvalidArgumentException;
use ipl\Stdlib\Filter;

class DownloadCommand extends Command
{
    /**
     * Download report with specified ID as PDF, CSV or JSON
     *
     * USAGE
     *
     *     icingacli reporting download <id> [--format=<pdf|csv|json>]
     *
     * OPTIONS
     *
     * --format=<pdf|csv|json>
     *     Download report as PDF, CSV or JSON. Defaults to pdf.
     *
     * --output=<file>
     *     Save report to the specified <file>.
     *
     * EXAMPLES
     *
     * Download report with ID 1:
     *     icingacli reporting download 1
     *
     * Download report with ID 1 as CSV:
     *     icingacli reporting download 1 --format=csv
     *
     * Download report with ID 1 as JSON to the specified file:
     *     icingacli reporting download 1 --format=json --output=sla.json
     */
    public function defaultAction()
    {
        $id = $this->params->getStandalone();
        if ($id === null) {
            $this->fail($this->translate('Argument id is mandatory'));
        }

        /** @var Model\Report $report */
        $report = Model\Report::on($this->getDb())
            ->with('timeframe')
            ->filter(Filter::equal('id', $id))
            ->first();

        if ($report === null) {
            throw new NotFoundError('Report not found');
        }

        $report = Report::fromModel($report);

        $format = strtolower($this->params->get('format', 'pdf'));
        switch ($format) {
            case 'pdf':
                $content = Pdfexport::first()->htmlToPdf($report->toPdf());
                break;
            case 'csv':
                $content = $report->toCsv();
                break;
            case 'json':
                $content = $report->toJson();
                break;
            default:
                throw new InvalidArgumentException(sprintf('Format %s is not supported', $format));
        }

        $output = $this->params->get('output');
        if ($output === null) {
            $name = sprintf(
                '%s (%s) %s',
                $report->getName(),
                $report->getTimeframe()->getName(),
                date('Y-m-d H:i')
            );

            $output = "$name.$format";
        } elseif (is_dir($output)) {
            $this->fail($this->translate(sprintf('%s is a directory', $output)));
        }

        file_put_contents($output, $content);
        echo "$output\n";
    }
}
