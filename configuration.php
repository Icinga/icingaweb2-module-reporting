<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting {

    use Icinga\Application\Version;

    /** @var \Icinga\Application\Modules\Module $this */

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

    $this->providePermission(
        'reporting/reports',
        $this->translate('Allow managing reports')
    );

    $this->providePermission(
        'reporting/schedules',
        $this->translate('Allow managing schedules')
    );

    $this->providePermission(
        'reporting/templates',
        $this->translate('Allow managing templates')
    );

    $this->providePermission(
        'reporting/timeframes',
        $this->translate('Allow managing timeframes')
    );

    $this->provideRestriction(
        'reporting/reports',
        $this->translate('Restrict access to the reports that match the provided filter')
    );
}
