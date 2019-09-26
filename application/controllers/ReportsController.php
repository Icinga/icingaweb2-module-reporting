<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\ReportForm;
use Icinga\Module\Reporting\Web\ReportsTimeframesAndTemplatesTabs;
use ipl\Html\Html;
use ipl\Sql\Select;
use reportingipl\Web\Url;
use reportingipl\Web\Widget\ButtonLink;

class ReportsController extends Controller
{
    use Database;
    use ReportsTimeframesAndTemplatesTabs;

    public function indexAction()
    {
        $this->createTabs()->activate('reports');

        $newReport = new ButtonLink(
            $this->translate('New Report'),
            Url::fromPath('reporting/reports/new')->getAbsoluteUrl('&'),
            'plus'
        );

        $this->addControl($newReport);

        $tableRows = [];

        $select = (new Select())
            ->from('report r')
            ->columns(['r.*', 'timeframe' => 't.name'])
            ->join('timeframe t', 'r.timeframe_id = t.id')
            ->orderBy('r.mtime', SORT_DESC);

        foreach ($this->getDb()->select($select) as $report) {
            $url = Url::fromPath('reporting/report', ['id' => $report->id])->getAbsoluteUrl('&');

            $tableRows[] = Html::tag('tr', ['href' => $url], [
                Html::tag('td', null, $report->name),
                Html::tag('td', null, $report->author),
                Html::tag('td', null, $report->timeframe),
                Html::tag('td', null, date('Y-m-d H:i', $report->ctime / 1000)),
                Html::tag('td', null, date('Y-m-d H:i', $report->mtime / 1000))
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                ['class' => 'common-table table-row-selectable', 'data-base-target' => '_next'],
                [
                    Html::tag(
                        'thead',
                        null,
                        Html::tag(
                            'tr',
                            null,
                            [
                                Html::tag('th', null, 'Name'),
                                Html::tag('th', null, 'Author'),
                                Html::tag('th', null, 'Timeframe'),
                                Html::tag('th', null, 'Date Created'),
                                Html::tag('th', null, 'Date Modified')
                            ]
                        )
                    ),
                    Html::tag('tbody', null, $tableRows)
                ]
            );

            $this->addContent($table);
        } else {
            $this->addContent(Html::tag('p', null, 'No reports created yet.'));
        }
    }

    public function newAction()
    {
        $this->setTitle($this->translate('New Report'));

        $form = new ReportForm();
        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/reports');

        $this->addContent($form);
    }
}
