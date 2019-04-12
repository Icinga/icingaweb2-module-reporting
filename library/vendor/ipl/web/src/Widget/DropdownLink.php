<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Widget;

use Icinga\Web\Url;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

class DropdownLink extends BaseHtmlElement
{
    protected $tag = 'div';

    protected $defaultAttributes = ['class' => 'dropdown'];

    /** @var array */
    protected $links = [];

    /**
     * Create a dropdown link
     *
     * @param   mixed                       $content
     * @param   \ipl\Html\Attributes|array  $attributes
     */
    public function __construct($content, $attributes = null)
    {
        $toggle = new ActionLink($content, '#');

        $toggle->getAttributes()->add([
            'class'         => 'dropdown-toggle',
            'role'          => 'button',
            'aria-haspopup' => true,
            'aria-expanded' => false
        ]);

        $this->hasBeenAssembled = true;

        $this
            ->setContent($toggle)
            ->getAttributes()
                ->add(Attributes::wantAttributes($attributes));

        $this->hasBeenAssembled = false;
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
        $link = new ActionLink($content, $url, $icon);

        $link->getAttributes()->add('class', 'dropdown-item');
        $link->getAttributes()->add('target', '_blank');

        $this->links[] = $link;

        return $this;
    }

    protected function assemble()
    {
        $this->add(Html::tag('div', ['class' => 'dropdown-menu'], $this->links));
    }
}
