<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Timeframe;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TimeframeForm;

class TimeframeController extends Controller
{
    use Database;

    /** @var Timeframe */
    protected $timeframe;

    public function init()
    {
        $this->timeframe = Timeframe::fromDb($this->params->getRequired('id'));
    }

    public function editAction()
    {
        $this->setTitle($this->translate('Edit Time Frame'));

        $values = [
            'name'  => $this->timeframe->getName(),
            'start' => $this->timeframe->getStart(),
            'end'   => $this->timeframe->getEnd()
        ];


        $form = (new TimeframeForm())
            ->setId($this->timeframe->getId());

        $form->populate($values);

        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/timeframes');

        $this->addContent($form);
    }
}
