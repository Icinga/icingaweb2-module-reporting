<?php
// Icinga Reporting | (c) 2020 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Downtime;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\DowntimesForm;

class DowntimeController extends Controller
{
    use Database;

    /** @var Downtime */
    protected $downtime;

    public function init()
    {
        $this->downtime = Downtime::fromDb($this->params->getRequired('id'));
    }

    public function editAction()
    {
        $this->setTitle($this->translate('Edit Downtime'));

        $values = [
            'object_id'  => $this->downtime->getObjectId(),
            'start_time' => $this->downtime->getStartTime(),
            'end_time'   => $this->downtime->getEndTime()
        ];

        $form = (new DowntimesForm())
            ->setId($this->downtime->getId());

        $form->populate($values);

        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/downtimes');

        $this->addContent($form);
    }
}