<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting {

    use Icinga\Application\Version;

    /** @var \Icinga\Application\Modules\Module $this */

    $this->provideCssFile('forms.less');
    $this->provideCssFile('system-report.css');

    if (version_compare(Version::VERSION, '2.9.0', '<')) {
        $this->provideJsFile('vendor/flatpickr.min.js');
        $this->provideCssFile('vendor/flatpickr.min.css');
    }

    $this->menuSection(N_('Reporting'))->add(N_('Reports'), array(
        'url' => 'reporting/reports',
    ));

    $this->provideConfigTab('backend', array(
        'title' => $this->translate('Configure the database backend'),
        'label' => $this->translate('Backend'),
        'url'   => 'config/backend'
    ));

    $this->provideConfigTab('mail', array(
        'title' => $this->translate('Configure mail'),
        'label' => $this->translate('Mail'),
        'url'   => 'config/mail'
    ));
}
