<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Module\Reporting\Actions\SendMail;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Report;
use ipl\Web\Compat\CompatForm;

class SendForm extends CompatForm
{
    use ProvidedReports;

    /** @var Report */
    protected $report;

    public function setReport(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    protected function assemble()
    {
        (new SendMail())->initConfigForm($this, $this->report);

        $this->addElement('submit', 'submit', [
            'label' => $this->translate('Send Report')
        ]);
    }

    public function onSuccess()
    {
        $values = $this->getValues();

        $sendMail = new SendMail();

        $sendMail->execute($this->report, $values);
    }
}
