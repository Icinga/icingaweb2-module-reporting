<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TimeframeForm;
use Icinga\Module\Reporting\Web\ReportsTimeframesAndTemplatesTabs;
use Icinga\Web\Notification;
use ipl\Html\Html;
use ipl\Web\Url;
use ipl\Web\Widget\ButtonLink;
use ipl\Web\Widget\Link;

class TimeframesController extends Controller
{
    use ReportsTimeframesAndTemplatesTabs;

    public function indexAction(): void
    {
        $this->createTabs()->activate('timeframes');

        $canManage = $this->hasPermission('reporting/timeframes');

        if ($canManage) {
            $this->addControl(
                (new ButtonLink(
                    $this->translate('New Timeframe'),
                    Url::fromPath('reporting/timeframes/new'),
                    'plus'
                ))->openInModal()
            );
        }

        $tableRows = [];

        $timeframes = Model\Timeframe::on(Database::get());

        $sortControl = $this->createSortControl(
            $timeframes,
            [
                'name'   => $this->translate('Name'),
                'ctime'  => $this->translate('Created At'),
                'mtime'  => $this->translate('Modified At')
            ]
        );

        $this->addControl($sortControl);

        foreach ($timeframes as $timeframe) {
            $subject = $timeframe->name;

            if ($canManage) {
                $subject = new Link(
                    $timeframe->name,
                    Url::fromPath('reporting/timeframe/edit', ['id' => $timeframe->id])
                );
            }

            $tableRows[] = Html::tag('tr', null, [
                Html::tag('td', null, $subject),
                Html::tag('td', null, $timeframe->start),
                Html::tag('td', null, $timeframe->end),
                Html::tag('td', null, $timeframe->ctime->format('Y-m-d H:i')),
                Html::tag('td', null, $timeframe->mtime->format('Y-m-d H:i'))
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                [
                    'class'            => 'common-table table-row-selectable',
                    'data-base-target' => '_next'
                ],
                [
                    Html::tag(
                        'thead',
                        null,
                        Html::tag(
                            'tr',
                            null,
                            [
                                Html::tag('th', null, 'Name'),
                                Html::tag('th', null, 'Start'),
                                Html::tag('th', null, 'End'),
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
            $this->addContent(Html::tag('p', null, 'No timeframes created yet.'));
        }
    }

    public function newAction(): void
    {
        $this->assertPermission('reporting/timeframes');
        $this->addTitleTab($this->translate('New Timeframe'));

        $form = (new TimeframeForm())
            ->setAction((string) Url::fromRequest())
            ->on(TimeframeForm::ON_SUCCESS, function () {
                Notification::success($this->translate('Created timeframe successfully'));

                $this->closeModalAndRefreshRelatedView(Url::fromPath('reporting/timeframes'));
            })->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
