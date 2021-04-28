<?php
// Icinga Reporting | (c) 2020 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\DowntimesForm;
use Icinga\Module\Reporting\Web\ReportsTimeframesAndTemplatesTabs;
use ipl\Html\Html;
use ipl\Sql\Select;
use ipl\Web\Url;
use reportingipl\Web\Widget\ButtonLink;
use Icinga\Module\Monitoring\Backend;



class DowntimesController extends Controller
{
    use Database;
    use ReportsTimeframesAndTemplatesTabs;

    public function indexAction()
    {
        $this->createTabs()->activate('downtimes');

        $new = new ButtonLink(
            $this->translate('New Downtime'),
            Url::fromPath('reporting/downtimes/new')->getAbsoluteUrl('&'),
            'plus'
        );

        $resource = MonitoringBackend::instance()->getName();

        $this->addControl($new);

        $select = (new Select())->from('icinga_reporting_fake_downtime')->columns(['*']);

        foreach ($this->getDb($resource)->select($select) as $downtime) {
            $url = Url::fromPath('reporting/downtime/edit', ['id' => $downtime->id])->getAbsoluteUrl('&');

            $tableRows[] = Html::tag('tr', ['href' => $url], [
                Html::tag('td', null, $downtime->object_id),
                Html::tag('td', null, $downtime->start_time),
                Html::tag('td', null, $downtime->end_time)
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
                                Html::tag('th', null, 'ObjectID'),
                                Html::tag('th', null, 'Start'),
                                Html::tag('th', null, 'End'),
                            ]
                        )
                    ),
                    Html::tag('tbody', null, $tableRows)
                ]
            );

            $this->addContent($table);
        } else {
            $this->addContent(Html::tag('p', null, 'No downtimes created yet.'));
        }
    }

    public function newAction()
    {
        $this->setTitle($this->translate('New Downtime'));

        $form = new DowntimesForm();

        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/downtimes');

        $this->addContent($form);
    }
}
