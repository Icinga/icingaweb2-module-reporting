<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting {

    use Icinga\Application\Icinga;

    require __DIR__ . '/vendor/autoload.php';

    /** @var \Icinga\Application\Modules\Module $this */

    $this->provideHook('reporting/Report', '\\Icinga\\Module\\Reporting\\Reports\\SystemReport');

    $this->provideHook('reporting/Action', '\\Icinga\\Module\\Reporting\\Actions\\SendMail');

    Icinga::app()->getLoader()->registerNamespace('reportingipl\Web', __DIR__ . '/library/vendor/ipl/web/src');
    Icinga::app()->getLoader()->registerNamespace('reportingipl\Html', __DIR__ . '/library/vendor/ipl/Html/src');
}
