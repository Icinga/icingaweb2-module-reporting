<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use ipl\Html\FormDecorator\DecoratorInterface;

trait DecoratedElement
{
    protected function addDecoratedElement(DecoratorInterface $decorator, $type, $name, array $attributes)
    {
        $element = $this->createElement($type, $name, $attributes);
        $decorator->decorate($element);
        $this->registerElement($element);
        $this->add($element);
    }
}
