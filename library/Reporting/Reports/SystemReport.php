<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Reports;

use Icinga\Application\Icinga;
use Icinga\Module\Reporting\Hook\ReportHook;
use Icinga\Module\Reporting\Timerange;
use ipl\Html\HtmlString;

class SystemReport extends ReportHook
{
    public function getName()
    {
        return 'System';
    }

    public function getHtml(Timerange $timerange, ?array $config = null)
    {
        ob_start();
        phpinfo();
        /** @var string $html */
        $html = ob_get_clean();

        if (! Icinga::app()->isCli()) {
            $doc = new \DOMDocument();
            @$doc->loadHTML($html);

            $style = $doc->getElementsByTagName('style')->item(0);
            $style->parentNode->removeChild($style);

            $title = $doc->getElementsByTagName('title')->item(0);
            $title->parentNode->removeChild($title);

            $meta = $doc->getElementsByTagName('meta')->item(0);
            $meta->parentNode->removeChild($meta);

            $doc->getElementsByTagName('div')->item(0)->setAttribute('class', 'system-report');

            $html = $doc->saveHTML();
        } else {
            $html = nl2br($html);
        }

        return new HtmlString($html);
    }
}
