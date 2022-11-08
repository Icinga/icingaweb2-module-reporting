<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Module\Reporting\Actions\SendMail;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Hook\EmailProviderHook;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\Web\Compat\CompatForm;

class SendForm extends CompatForm
{
    use Database;
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
        $this->setDefaultElementDecorator(new CompatDecorator());

        (new SendMail())->initConfigForm($this, $this->report);

        $radio = $this->addElement('radio', 'source_radio', [
            'label'   => 'E-Mail Source',
            'options' => [
                'manual'   => 'Manual',
                'contacts' => 'Contacts',
            ],
            'value'   => 'contacts',
            'class'   => 'autosubmit'
        ]);

        if ($radio->getValue('source_radio') === 'contacts') {
            $emails = [null => 'Select Contacts'];
            foreach (EmailProviderHook::getProvider() as $provider) {
                var_dump($provider);
                $emails = array_merge($emails, $provider->getContactEmails());
            }

            $this->addElement('select', 'emails_list', [
                'multiple' => true,
                'label'    => 'Contacts',
                'options'  => $emails
            ]);
        } else {
            $this->addElement('textarea', 'emails_manual', [
                'label' => 'Contact E-Mails'
            ]);
        }

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
