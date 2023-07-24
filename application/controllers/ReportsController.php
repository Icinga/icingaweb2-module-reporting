<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Module\Icingadb\ProvidedHook\Reporting\HostSlaReport;
use Icinga\Module\Icingadb\ProvidedHook\Reporting\ServiceSlaReport;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model\Report;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\ReportForm;
use Icinga\Module\Reporting\Web\ReportsTimeframesAndTemplatesTabs;
use Icinga\Web\Notification;
use ipl\Html\Html;
use ipl\Web\Url;
use ipl\Web\Widget\ButtonLink;

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

        $reports = Report::on($this->getDb())
            ->withColumns(['report.timeframe.name']);

        $sortControl = $this->createSortControl(
            $reports,
            [
                'name'   => $this->translate('Name'),
                'author' => $this->translate('Author'),
                'ctime'  => $this->translate('Created At'),
                'mtime'  => $this->translate('Modified At')
            ]
        );

        $this->addControl($sortControl);

        /** @var Report $report */
        foreach ($reports as $report) {
            $url = Url::fromPath('reporting/report', ['id' => $report->id])->getAbsoluteUrl('&');

            $tableRows[] = Html::tag('tr', ['href' => $url], [
                Html::tag('td', null, $report->name),
                Html::tag('td', null, $report->author),
                Html::tag('td', null, $report->timeframe->name),
                Html::tag('td', null, $report->ctime->format('Y-m-d H:i')),
                Html::tag('td', null, $report->mtime->format('Y-m-d H:i')),
                Html::tag('td', null, $report->mtime->format('Y-m-d H:i'))
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

        switch ($this->params->shift('report')) {
            case 'host':
                $class = HostSlaReport::class;
                break;
            case 'service':
                $class = ServiceSlaReport::class;
                break;
            default:
                $class = null;
                break;
        }

        $form = (new ReportForm())
            ->setAction((string) Url::fromRequest())
            ->setRenderCreateAndShowButton($class !== null)
            ->populate([
                'filter'    => $this->params->shift('filter'),
                'reportlet' => $class
            ])
            ->on(ReportForm::ON_SUCCESS, function (ReportForm $form) {
                Notification::success($this->translate('Created report successfully'));

                $pressedButton = $form->getPressedSubmitElement();
                if ($pressedButton && $pressedButton->getName() !== 'create_show') {
                    $this->closeModalAndRefreshRelatedView(Url::fromPath('reporting/reports'));
                } else {
                    $this->redirectNow(
                        Url::fromPath(
                            sprintf(
                                'reporting/reports#!%s',
                                Url::fromPath('reporting/report', ['id' => $form->getId()])->getAbsoluteUrl()
                            )
                        )
                    );
                }
            })
            ->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
