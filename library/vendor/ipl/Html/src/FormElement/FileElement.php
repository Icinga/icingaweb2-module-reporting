<?php

namespace reportingipl\Html\FormElement;

use ipl\Html\FormElement\InputElement;

class FileElement extends InputElement
{
    protected $type = 'file';

    public function setValue($value)
    {
        return $this;
    }
}
