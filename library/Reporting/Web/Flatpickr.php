<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

use Icinga\Application\Version;
use ipl\Html\Html;
use ipl\Web\Compat\CompatDecorator;

class Flatpickr extends CompatDecorator
{
    protected $allowInput = true;

    /**
     * Set whether to allow manual input
     *
     * @param bool $state
     *
     * @return $this
     */
    public function setAllowInput(bool $state): self
    {
        $this->allowInput = $state;

        return $this;
    }

    protected function assembleElement()
    {
        if (version_compare(Version::VERSION, '2.9.0', '>=')) {
            $element = parent::assembleElement();
        } else {
            $element = $this->formElement;
        }

        if (version_compare(Version::VERSION, '2.10.0', '<')) {
            $element->getAttributes()->set('data-use-flatpickr-fallback', true);
        } else {
            $element->getAttributes()->set('data-use-datetime-picker', true);
        }

        if (! $this->allowInput) {
            return $element;
        }

        $element->getAttributes()
            ->set('data-input', true)
            ->set('data-flatpickr-wrap', true)
            ->set('data-flatpickr-allow-input', true)
            ->set('data-flatpickr-click-opens', 'false');

        return [
            $element,
            Html::tag('button', ['type' => 'button', 'class' => 'icon-calendar', 'data-toggle' => true]),
            Html::tag('button', ['type' => 'button', 'class' => 'icon-cancel', 'data-clear' => true])
        ];
    }

    protected function assemble()
    {
        if (version_compare(Version::VERSION, '2.9.0', '>=')) {
            parent::assemble();
            return;
        }

        if ($this->formElement->hasBeenValidated() && ! $this->formElement->isValid()) {
            $this->getAttributes()->add('class', 'has-error');
        }

        $this->add(array_filter([
            $this->assembleLabel(),
            $this->assembleElement(),
            $this->assembleDescription(),
            $this->assembleErrors()
        ]));
    }
}
