<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TimeframeForm;
use Icinga\Module\Reporting\Web\ReportsTimeframesAndTemplatesTabs;
use ipl\Html\Html;
use ipl\Sql\Select;
use ipl\Web\Url;
use ipl\Web\Widget\ButtonLink;
use ipl\Web\Widget\Link;

class TimeframesController extends Controller
{
    use Database;
    use ReportsTimeframesAndTemplatesTabs;

    public function indexAction()
    {
        $this->createTabs()->activate('timeframes');

        $canManage = $this->hasPermission('reporting/timeframes');

        if ($canManage) {
            $this->addControl(new ButtonLink(
                $this->translate('New Timeframe'),
                Url::fromPath('reporting/timeframes/new'),
                'plus',
                [
                    'data-icinga-modal'   => true,
                    'data-no-icinga-ajax' => true
                ]
            ));
        }

        $tableRows = [];

        $select = (new Select())
            ->from('timeframe t')
            ->columns('*');

        foreach ($this->getDb()->select($select) as $timeframe) {
            $subject = $timeframe->name;

            if ($canManage) {
                $subject = new Link(
                    $timeframe->name,
                    Url::fromPath('reporting/timeframe/edit', ['id' => $timeframe->id]),
                    [
                        'data-icinga-modal'   => true,
                        'data-no-icinga-ajax' => true
                    ]
                );
            }

            $tableRows[] = Html::tag('tr', [
                'data-icinga-modal'   => true,
                'data-no-icinga-ajax' => true
            ], [
                Html::tag('td', null, $subject),
                Html::tag('td', null, $timeframe->start),
                Html::tag('td', null, $timeframe->end),
                Html::tag('td', null, date('Y-m-d H:i', $timeframe->ctime / 1000)),
                Html::tag('td', null, date('Y-m-d H:i', $timeframe->mtime / 1000))
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                ['class' => 'common-table table-row-selectable'],
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

    public function newAction()
    {
        $this->assertPermission('reporting/timeframes');
        $this->addTitleTab($this->translate('New Timeframe'));

        $form = (new TimeframeForm())
            ->setAction((string) Url::fromRequest())
            ->on(TimeframeForm::ON_SUCCESS, function () {
                $this->getResponse()->setHeader('X-Icinga-Container', 'modal-content', true);

                $this->redirectNow('__CLOSE__');
            })->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
