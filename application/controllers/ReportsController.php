<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\ReportForm;
use Icinga\Module\Reporting\Web\ReportsTimeframesAndTemplatesTabs;
use ipl\Html\Html;
use ipl\Sql\Select;
use ipl\Web\Url;
use ipl\Web\Widget\ButtonLink;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;

class ReportsController extends Controller
{
    use Database;
    use ReportsTimeframesAndTemplatesTabs;

    public function indexAction()
    {
        $this->createTabs()->activate('reports');

        if ($this->hasPermission('reporting/reports')) {
            $this->addControl(new ButtonLink(
                $this->translate('New Report'),
                Url::fromPath('reporting/reports/new'),
                'plus',
                [
                    'data-icinga-modal'   => true,
                    'data-no-icinga-ajax' => true
                ]
            ));
        }

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
                Html::tag('td', null, date('Y-m-d H:i', $report->mtime / 1000)),
                Html::tag('td', ['class' => 'icon-col'], [
                    new Link(
                        new Icon('edit'),
                        Url::fromPath('reporting/report/edit', ['id' => $report->id]),
                        [
                            'data-icinga-modal'   => true,
                            'data-no-icinga-ajax' => true
                        ]
                    )
                ])
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
                                Html::tag('th', null, 'Date Modified'),
                                Html::tag('th')
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
        $this->assertPermission('reporting/reports');
        $this->addTitleTab($this->translate('New Report'));

        $form = (new ReportForm())
            ->setAction((string) Url::fromRequest())
            ->on(ReportForm::ON_SUCCESS, function () {
                $this->getResponse()->setHeader('X-Icinga-Container', 'modal-content', true);

                $this->redirectNow('__CLOSE__');
            })
            ->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
