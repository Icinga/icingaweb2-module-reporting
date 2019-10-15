<?php

namespace Icinga\Module\Reporting\Web\Widget;

use Icinga\Module\Reporting\Common\Macros;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

class CoverPage extends BaseHtmlElement
{
    use Macros;

    /** @var array */
    protected $backgroundImage;

    /** @var string */
    protected $color;

    /** @var array */
    protected $logo;

    /** @var string */
    protected $title;

    protected $tag = 'div';

    protected $defaultAttributes = ['class' => 'cover-page page'];

    /**
     * @return bool
     */
    public function hasBackgroundImage()
    {
        return $this->backgroundImage !== null;
    }

    /**
     * @return array
     */
    public function getBackgroundImage()
    {
        return $this->backgroundImage;
    }

    /**
     * @param array $backgroundImage
     *
     * @return $this
     */
    public function setBackgroundImage($backgroundImage)
    {
        $this->backgroundImage = $backgroundImage;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasColor()
    {
        return $this->color !== null;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLogo()
    {
        return $this->logo !== null;
    }

    /**
     * @return array
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param array $logo
     *
     * @return $this
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    public function hasTitle()
    {
        return $this->title !== null;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    protected function assemble()
    {
        if ($this->hasBackgroundImage()) {
            $this
                ->getAttributes()
                ->add('style', "background-image: url('" . Template::getDataUrl($this->getBackgroundImage()) . "');");
        }

        $content = Html::tag('div', ['class' => 'cover-page-content']);

        if ($this->hasColor()) {
            $content->getAttributes()->add('style', "color: {$this->getColor()};");
        }

        if ($this->hasLogo()) {
            $content->add(Html::tag(
                'img',
                [
                    'class' => 'logo',
                    'src'   => Template::getDataUrl($this->getLogo())
                ]
            ));
        }

        if ($this->hasTitle()) {
            $title = array_map(function ($part) {
                $part = trim($part);

                if (! $part) {
                    return Html::tag('br');
                } else {
                    return Html::tag('div', null, $part);
                }
            }, explode("\n", $this->resolveMacros($this->getTitle())));

            $content->add(Html::tag(
                'h2',
                $title
            ));
        }

        $this->add($content);
    }
}
