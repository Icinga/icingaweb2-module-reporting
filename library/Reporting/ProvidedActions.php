<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

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
