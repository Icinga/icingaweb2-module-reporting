<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

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
