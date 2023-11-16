<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Exception;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Timeframe;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TimeframeForm;
use Icinga\Web\Notification;
use ipl\Html\Form;
use ipl\Web\Url;
use ipl\Stdlib\Filter;

class TimeframeController extends Controller
{
    /** @var Timeframe */
    protected $timeframe;

    public function init()
    {
        /** @var Model\Timeframe $timeframe */
        $timeframe = Model\Timeframe::on(Database::get())
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
            ->on(TimeframeForm::ON_SUCCESS, function (Form $form) {
                $pressedButton = $form->getPressedSubmitElement();
                if ($pressedButton && $pressedButton->getName() === 'remove') {
                    Notification::success($this->translate('Removed timeframe successfully'));
                } else {
                    Notification::success($this->translate('Update timeframe successfully'));
                }

                $this->switchToSingleColumnLayout();
            })->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }
}
