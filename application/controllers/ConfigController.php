<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Reporting\Forms\ConfigureMailForm;
use Icinga\Module\Reporting\Forms\SelectBackendForm;
use Icinga\Web\Controller;

class ConfigController extends Controller
{
    public function init()
    {
        $this->assertPermission('config/modules');

        parent::init();
    }

    public function backendAction()
    {
        $form = (new SelectBackendForm())
            ->setIniConfig(Config::module('reporting'));

        $form->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('backend');
        $this->view->form = $form;
    }

    public function mailAction()
    {
        $form = (new ConfigureMailForm())
            ->setIniConfig(Config::module('reporting'));

        $form->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('mail');
        $this->view->form = $form;
    }
}
