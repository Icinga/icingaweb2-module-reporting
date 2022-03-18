<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Forms;

use Icinga\Data\ResourceFactory;
use Icinga\Forms\ConfigForm;

class SelectBackendForm extends ConfigForm
{
    public function init()
    {
        $this->setName('reporting_backend');
        $this->setSubmitLabel($this->translate('Save Changes'));
    }

    public function createElements(array $formData)
    {
        $dbResources = ResourceFactory::getResourceConfigs('db')->keys();
        $options = array_combine($dbResources, $dbResources);

        $default = null;
        if (isset($options['reporting'])) {
            $default = 'reporting';
        }

        $this->addElement('select', 'backend_resource', [
            'label'         => $this->translate('Database'),
            'description'   => $this->translate('Database resource'),
            'multiOptions'  => $options,
            'value'         => $default,
            'required'      => true
        ]);
    }
}
