<?php

// Icinga Reporting | (c) 2021 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Widget;

use ipl\Web\Widget\ActionLink;
use ipl\Web\Widget\Dropdown;

class CompatDropdown extends Dropdown
{
    public function addLink($content, $url, $icon = null, ?array $attributes = null)
    {
        $link = new ActionLink($content, $url, $icon, ['class' => 'dropdown-item']);
        if (! empty($attributes)) {
            $link->addAttributes($attributes);
        }

        $this->links[] = $link;

        return $this;
    }
}
