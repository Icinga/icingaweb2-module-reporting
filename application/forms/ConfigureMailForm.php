<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

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
