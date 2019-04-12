<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Widget;

use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use reportingipl\Web\Common\BaseTarget;
use reportingipl\Web\Url;

class Link extends BaseHtmlElement
{
    use BaseTarget;

    protected $tag = 'a';

    /** @var Url */
    protected $url;

    /**
     * Create a link element
     *
     * @param   mixed                       $content
     * @param   Url|string                  $url
     * @param   \ipl\Html\Attributes|array  $attributes
     */
    public function __construct($content, $url, $attributes = null)
    {
        $this->hasBeenAssembled = true;

        $this
            ->setContent($content)
            ->setUrl($url)
            ->getAttributes()
                ->add(Attributes::wantAttributes($attributes))
                ->registerAttributeCallback('href', [$this, 'getHrefAttribute']);

        $this->hasBeenAssembled = false;
    }

    /**
     * Get the URL of the link
     *
     * @return  Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the URL of the link
     *
     * @param   Url|string $url
     *
     * @return  $this
     */
    public function setUrl($url)
    {
        if (! $url instanceof \Icinga\Web\Url) {
            try {
                $url = Url::fromPath($url);
            } catch (\Exception $e) {
                $url = "Invalid: {$e->getMessage()}";
            }
        }

        $this->url = $url;

        return $this;
    }

    /**
     * @return  Attribute
     */
    public function getHrefAttribute()
    {
        return new Attribute('href', (string) $this->getUrl());
    }
}
