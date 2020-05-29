<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
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
                'plus'
            ));
        }

        $tableRows = [];

        $select = (new Select())
            ->from('timeframe t')
            ->columns('*');

        foreach ($this->getDb()->select($select) as $timeframe) {
            $subject = $timeframe->name;

            if ($canManage) {
                $subject = new Link($timeframe->name, Url::fromPath(
                    'reporting/timeframe/edit',
                    ['id' => $timeframe->id]
                ));
            }

            $tableRows[] = Html::tag('tr', null, [
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

        $form = new TimeframeForm();
        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/timeframes');

        $this->addContent($form);
    }
}
