<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Forms;

use Icinga\Forms\ConfigForm;

class ConfigureMailForm extends ConfigForm
{
    public function init()
    {
        $this->setName('reporting_mail');
        $this->setSubmitLabel($this->translate('Save Changes'));
    }

    public function createElements(array $formData)
    {
        $this->addElement('text', 'mail_from', [
            'label'       => $this->translate('From'),
            'placeholder' => 'reporting@icinga'
        ]);
    }
}
