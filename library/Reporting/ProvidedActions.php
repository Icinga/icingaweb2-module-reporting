<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Module\Reporting\Hook\ActionHook;

trait ProvidedActions
{
    public function listActions()
    {
        $actions = [];

        foreach (ActionHook::getActions() as $class => $action) {
            $actions[$class] = $action->getName();
        }

        return $actions;
    }
}
