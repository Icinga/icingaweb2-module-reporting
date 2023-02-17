<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Exception;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Timeframe;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TimeframeForm;
use ipl\Web\Url;
use ipl\Stdlib\Filter;

class TimeframeController extends Controller
{
    use Database;

    /** @var Timeframe */
    protected $timeframe;

    public function init()
    {
        $timeframe = Model\Timeframe::on($this->getDb())
            ->filter(Filter::equal('id', $this->params->getRequired('id')))
            ->first();

        if ($timeframe === null) {
            throw new Exception('Timeframe not found');
        }

        $this->timeframe = Timeframe::fromModel($timeframe);
    }

    public function editAction()
    {
        $this->assertPermission('reporting/timeframes');
        $this->addTitleTab($this->translate('Edit Time Frame'));

        $values = [
            'name'  => $this->timeframe->getName(),
            'start' => $this->timeframe->getStart(),
            'end'   => $this->timeframe->getEnd()
        ];

        $form = TimeframeForm::fromId($this->timeframe->getId())
            ->setAction((string) Url::fromRequest())
            ->populate($values)
            ->on(TimeframeForm::ON_SUCCESS, function () {
                $this->getResponse()->setHeader('X-Icinga-Container', 'modal-content', true);

                $this->redirectNow('__CLOSE__');
            })->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
