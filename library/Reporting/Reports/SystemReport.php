<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Reports;

use Icinga\Module\Reporting\Hook\ReportHook;
use Icinga\Module\Reporting\Timerange;
use ipl\Html\HtmlString;

class SystemReport extends ReportHook
{
    public function getName()
    {
        return 'System';
    }

    public function getHtml(Timerange $timerange, array $config = null)
    {
        ob_start();
        phpinfo();
        $html = ob_get_clean();

        $doc = new \DOMDocument();
        @$doc->loadHTML($html);

        $style = $doc->getElementsByTagName('style')->item(0);
        $style->parentNode->removeChild($style);

        $title = $doc->getElementsByTagName('title')->item(0);
        $title->parentNode->removeChild($title);

        $meta = $doc->getElementsByTagName('meta')->item(0);
        $meta->parentNode->removeChild($meta);

        $doc->getElementsByTagName('div')->item(0)->setAttribute('class', 'system-report');

        return new HtmlString($doc->saveHTML());
    }
}
