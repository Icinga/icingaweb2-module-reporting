<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Widget;

use ipl\Html\Attributes;
use reportingipl\Web\Url;

class ActionLink extends Link
{
    protected $defaultAttributes = ['class' => 'action-link'];

    /**
     * Create a action link
     *
     * @param   mixed               $content
     * @param   Url|string          $url
     * @param   string              $icon
     * @param   Attributes|array    $attributes
     */
    public function __construct($content, $url, $icon = null, $attributes = null)
    {
        parent::__construct($content, $url, $attributes);

        if ($icon !== null) {
            $this->prepend(new Icon($icon));
        }
    }
}
