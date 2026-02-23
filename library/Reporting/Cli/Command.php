<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting\Cli;

use Icinga\Application\Icinga;
use Icinga\Application\Version;

class Command extends \Icinga\Cli\Command
{
    // Fix Web 2 issue where $configs is not properly initialized
    protected $configs = [];

    public function init()
    {
        if (version_compare(Version::VERSION, '2.7.0', '<')) {
            Icinga::app()->getModuleManager()->loadEnabledModules();
        }
    }
}
