<?php

namespace Icinga\Module\Reporting\Reports;

use Icinga\Application\Icinga;
use Icinga\Module\Icingadb\ProvidedHook\Reporting\HostSlaReport;
use Icinga\Module\Icingadb\ProvidedHook\Reporting\ServiceSlaReport;
use Icinga\Module\Icingadb\Widget\EmptyState;
use Icinga\Module\Reporting\Timerange;
use ipl\Html\Html;
use function ipl\I18n\t;

class TotalServiceSlaReport extends ServiceSlaReport
{
    public function getName()
    {
        $name = t('Total Service SLA');
        if (Icinga::app()->getModuleManager()->hasEnabled('idoreports')) {
            $name .= ' (Icinga DB)';
        }

        return $name;
    }

    public function getHtml(Timerange $timerange, array $config = null)
    {
        $data = $this->getData($timerange, $config);

        if (! count($data)) {
            return new EmptyState(t('No data found.'));
        }

        $threshold = isset($config['threshold']) ? (float) $config['threshold'] : static::DEFAULT_THRESHOLD;

        $tableRows = [];
        $precision = $config['sla_precision'] ?? static::DEFAULT_REPORT_PRECISION;

        // We only have one average
        $average = $data->getAverages()[0];

        if ($average < $threshold) {
            $slaClass = 'nok';
        } else {
            $slaClass = 'ok';
        }

        $total = $this instanceof HostSlaReport
            ? sprintf(t('Total (%d Hosts)'), $data->count())
            : sprintf(t('Total (%d Services)'), $data->count());

        $tableRows[] = Html::tag('tr', null, [
            Html::tag('td', ['colspan' => count($data->getDimensions())], $total),
            Html::tag('td', ['class' => "sla-column $slaClass"], round($average, $precision))
        ]);

        $table = Html::tag(
            'table',
            ['class' => 'common-table sla-table'],
            [Html::tag('tbody', null, $tableRows)]
        );

        return $table;
    }
}
