<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

trait ReportsAndTimeframesTabs
{
    /**
     * Create tabs
     *
     * @return  \Icinga\Web\Widget\Tabs
     */
    protected function createTabs()
    {
        $tabs = $this->getTabs();

        $tabs->add('reports', [
                'title'     => $this->translate('Show reports'),
                'label'     => $this->translate('Reports'),
                'url'       => 'reporting/reports'
        ]);

        $tabs->add('timeframes', [
            'title'     => $this->translate('Show time frames'),
            'label'     => $this->translate('Time Frames'),
            'url'       => 'reporting/timeframes'
        ]);

        return $tabs;
    }
}
