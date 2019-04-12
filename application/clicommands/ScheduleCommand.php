<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Clicommands;

use Icinga\Module\Reporting\Cli\Command;
use Icinga\Module\Reporting\Scheduler;

class ScheduleCommand extends Command
{
    /**
     * Run all configured reports based on their schedule
     *
     * USAGE:
     *
     *   icingacli reporting schedule run
     */
    public function runAction()
    {
        $scheduler = new Scheduler($this->getDb());

        $scheduler->run();
    }
}
