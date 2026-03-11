<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Reporting\Forms\ConfigureMailForm;
use Icinga\Module\Reporting\Forms\SelectBackendForm;
use Icinga\Web\Controller;

class ConfigController extends Controller
{
    public function init(): void
    {
        $this->assertPermission('config/modules');

        parent::init();
    }

    public function backendAction(): void
    {
        $form = (new SelectBackendForm())
            ->setIniConfig(Config::module('reporting'));

        $form->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('backend');
        $this->view->form = $form;
    }

    public function mailAction(): void
    {
        $form = (new ConfigureMailForm())
            ->setIniConfig(Config::module('reporting'));

        $form->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('mail');
        $this->view->form = $form;
    }
}
