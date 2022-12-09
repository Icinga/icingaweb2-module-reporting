<?php

// Icinga Reporting | (c) 2021 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms\Decorator;

use Icinga\Application\Version;
use ipl\Html\Attributes;
use ipl\Html\FormElement\CheckboxElement;
use ipl\Html\HtmlElement;

class CompatDecorator extends \ipl\Web\Compat\CompatDecorator
{
    protected function createCheckboxCompat(CheckboxElement $checkbox)
    {
        if (! $checkbox->getAttributes()->has('id')) {
            $checkbox->setAttribute('id', base64_encode(random_bytes(8)));
        }

        $checkbox->getAttributes()->add('class', 'sr-only');

        $classes = ['toggle-switch'];
        if ($checkbox->getAttributes()->get('disabled')->getValue()) {
            $classes[] = 'disabled';
        }

        return [
            $checkbox,
            new HtmlElement('label', Attributes::create([
                'class'       => $classes,
                'aria-hidden' => 'true',
                'for'         => $checkbox->getAttributes()->get('id')->getValue()
            ]), new HtmlElement('span', Attributes::create(['class' => 'toggle-slider'])))
        ];
    }

    protected function assembleElementCompat()
    {
        if ($this->formElement instanceof CheckboxElement) {
            return $this->createCheckboxCompat($this->formElement);
        }

        return $this->formElement;
    }

    protected function assemble()
    {
        if (version_compare(Version::VERSION, '2.9.0', '>=')) {
            parent::assemble();

            return;
        }

        if ($this->formElement->hasMessages()) {
            $this->getAttributes()->add('class', 'has-error');
        }

        $this->add(array_filter([
            $this->assembleLabel(),
            $this->assembleElementCompat(),
            $this->assembleDescription(),
            $this->assembleErrors()
        ]));
    }
}
