<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Cli;

use Icinga\Application\Icinga;
use Icinga\Module\Reporting\Database;

class Command extends \Icinga\Cli\Command
{
    use Database;

    // Fix Web 2 issue where $configs is not properly initialized
    protected $configs = [];

    public function init()
    {
        $mm = Icinga::app()->getModuleManager();
        foreach ($mm->getModule($this->getModuleName())->getDependencies() as $module => $_) {
            if ($mm->hasInstalled($module)) {
                $mm->loadModule($module);
            }
        }
    }
}
