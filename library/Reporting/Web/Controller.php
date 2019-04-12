<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

use ipl\Html\Form;
use reportingipl\Web\Compat\CompatController;

class Controller extends CompatController
{
    protected function redirectForm(Form $form, $url)
    {
        if ($form->hasBeenSubmitted()
            && ((isset($form->valid) && $form->valid === true)
                || $form->isValid())
        ) {
            $this->redirectNow($url);
        }
    }
}
