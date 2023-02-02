<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Module\Reporting\Actions\SendMail;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Hook\EmailProviderHook;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\I18n\Translation;
use ipl\Web\Compat\CompatForm;

class SendForm extends CompatForm
{
    use Database;
    use ProvidedReports;
    use Translation;

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

        $this->addElement('radio', 'source_radio', [
            'label'   => $this->translate('E-Mail Source'),
            'options' => [
                'manual'   => $this->translate('Manual input'),
                'contacts' => $this->translate('Contacts'),
            ],
            'value'   => 'contacts',
            'class'   => 'autosubmit'
        ]);

        if ($this->getPopulatedValue('source_radio', 'contacts') === 'contacts') {
            $emails = [null => $this->translate('Select Contacts')];
            foreach (EmailProviderHook::getProviders() as $provider) {
                $emails = array_merge($emails, $provider->getContactEmails());
            }

            $this->addElement('select', 'emails_list', [
                'multiple' => true,
                'label'    => $this->translate('Contacts'),
                'options'  => $emails
            ]);
        } else {
            $this->addElement('textarea', 'emails_manual', [
                'label' => $this->translate('Contact E-Mails')
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
