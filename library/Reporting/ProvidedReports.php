<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Module\Reporting\Hook\ReportHook;

trait ProvidedReports
{
    public function listReports()
    {
        $reports = [];

        foreach (ReportHook::getReports() as $class => $report) {
            $reports[$class] = $report->getName();
        }

        return $reports;
    }
}
