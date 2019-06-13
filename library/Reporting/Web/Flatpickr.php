<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\Html;

class Flatpickr extends DivDecorator
{
    protected $defaultAttributes = ['class' => 'reporting-flatpickr'];

    public function decorate(BaseFormElement $input)
    {
        parent::decorate($input);

        $input->getAttributes()->set('data-input', true);
    }

    protected function afterFormElement()
    {
        $this->add(Html::tag('button', ['type' => 'button', 'class' => 'icon-calendar', 'data-toggle' => true]));
        $this->add(Html::tag('button', ['type' => 'button', 'class' => 'icon-cancel', 'data-clear' => true]));
    }
}
