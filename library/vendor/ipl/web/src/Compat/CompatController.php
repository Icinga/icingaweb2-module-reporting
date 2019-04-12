<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Compat;

use Icinga\Web\Controller;
use ipl\Html\ValidHtml;
use reportingipl\Web\Widget\Content;
use reportingipl\Web\Widget\Controls;
use reportingipl\Web\Widget\Tabs;

class CompatController extends Controller
{
    /** @var Controls */
    protected $controls;

    /** @var Content */
    protected $content;

    /** @var Tabs */
    protected $tabs;

    protected function prepareInit()
    {
        parent::prepareInit();

        unset($this->view->tabs);

        $this->controls = new Controls();
        $this->content = new Content();
        $this->tabs = new Tabs();

        $this->controls->setTabs($this->tabs);

        ViewRenderer::inject();

        $this->view->controls = $this->controls;
        $this->view->content = $this->content;
    }

    /**
     * Get the tabs
     *
     * @return  Tabs
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    protected function addControl(ValidHtml $control)
    {
        $this->controls->add($control);

        return $this;
    }

    protected function addContent(ValidHtml $content)
    {
        $this->content->add($content);

        return $this;
    }

    protected function setTitle($title, ...$args)
    {
        $title = vsprintf($title, $args);

        $this->view->title = $title;

        $this->getTabs()->add(uniqid(), [
            'active'    => true,
            'label'     => $title,
            'url'       => $this->getRequest()->getUrl()
        ]);
    }
}
