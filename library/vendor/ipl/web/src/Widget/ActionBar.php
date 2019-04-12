<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Widget;

use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use reportingipl\Web\Common\BaseTarget;
use reportingipl\Web\Url;

class ActionBar extends BaseHtmlElement
{
    use BaseTarget;

    protected $contentSeparator = ' ';

    protected $tag = 'div';

    protected $defaultAttributes = ['class' => 'action-bar', 'data-base-target' => '_self'];

    /**
     * Create a action bar
     *
     * @param   Attributes|array    $attributes
     */
    public function __construct($attributes = null)
    {
        $this->getAttributes()->add(Attributes::wantAttributes($attributes));
    }

    /**
     * @param   mixed       $content
     * @param   Url|string  $url
     * @param   string      $icon
     *
     * @return  $this
     */
    public function addLink($content, $url, $icon = null)
    {
        $this->add(new ActionLink($content, $url, $icon));

        return $this;
    }
}
