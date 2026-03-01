<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting\Hook;

use Icinga\Application\Hook;
use Icinga\Module\Reporting\Report;
use ipl\Html\Form;

abstract class ActionHook
{
    /**
     * @return  string
     */
    abstract public function getName();

    /**
     * @param Report $report
     * @param array  $config
     */
    abstract public function execute(Report $report, array $config);

    /**
     * @param Form   $form
     * @param Report $report
     */
    public function initConfigForm(Form $form, Report $report)
    {
    }

    /**
     * @return  ActionHook[]
     */
    final public static function getActions()
    {
        return Hook::all('reporting/Action');
    }
}
