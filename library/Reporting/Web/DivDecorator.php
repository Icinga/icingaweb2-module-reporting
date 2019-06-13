<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\FormDecorator\DecoratorInterface;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\FormElement\SubmitElement;
use ipl\Html\Html;

class DivDecorator extends BaseHtmlElement implements DecoratorInterface
{
    protected $tag = 'div';

    /** @var BaseFormElement */
    protected $formElement;

    /** @var bool */
    protected $formElementAdded = false;

    /**
     * Set the form element to decorate
     *
     * @param   BaseFormElement $formElement
     *
     * @return  static
     */
    public function decorate(BaseFormElement $formElement)
    {
        $decorator = clone $this;

        $decorator->formElement = $formElement;

        // TODO(el): Should be SubmitElementInterface
        if ($formElement instanceof SubmitElement) {
            $class = 'form-control';
        } else {
            $class = 'form-element';
        }

        $decorator->getAttributes()->add('class', $class);

        $formElement->prependWrapper($decorator);

        return $decorator;
    }

    public function add($content)
    {
        if ($content === $this->formElement) {
            if ($this->formElementAdded) {
                return $this;
            }

            $this->formElementAdded = true;
        }

        parent::add($content);

        return $this;
    }

    protected function assemble()
    {
        if ($this->formElement->hasBeenValidatedAndIsNotValid()) {
            $this->getAttributes()->add('class', 'has-error');
        }

        $this->add([
            $this->assembleLabel(),
            $this->formElement
        ]);

        $this->afterFormElement();

        $this->add([
            $this->assembleDescription(),
            $this->assembleErrors()
        ]);
    }

    protected function assembleLabel()
    {
        $label = $this->formElement->getLabel();

        if ($label !== null) {
            $attributes = null;

            if ($this->formElement->getAttributes()->has('id')) {
                $attributes = new Attributes(['for' => $this->formElement->getAttributes()->get('id')]);
            }

            return Html::tag('label', $attributes, $label);
        }

        return null;
    }

    protected function assembleDescription()
    {
        $description = $this->formElement->getDescription();

        if ($description !== null) {
            return Html::tag('p', ['class' => 'form-element-description'], $description);
        }

        return null;
    }

    protected function assembleErrors()
    {
        $errors = [];

        foreach ($this->formElement->getMessages() as $message) {
            $errors[] = Html::tag('p', ['class' => 'form-element-error'], $message);
        }

        if (! empty($errors)) {
            return $errors;
        }

        return null;
    }

    protected function afterFormElement()
    {

    }
}
