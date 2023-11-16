<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Widget;

use Icinga\Module\Reporting\Common\Macros;
use Icinga\Module\Reporting\Model;
use ipl\Html\BaseHtmlElement;

class Template extends BaseHtmlElement
{
    use Macros;

    protected $tag = 'div';

    protected $defaultAttributes = ['class' => 'template'];

    /** @var CoverPage */
    protected $coverPage;

    /** @var HeaderOrFooter */
    protected $header;

    /** @var HeaderOrFooter */
    protected $footer;

    protected $preview;

    public static function getDataUrl(array $image = null)
    {
        if (empty($image)) {
            return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        }

        return sprintf('data:%s;base64,%s', $image['mime_type'], $image['content']);
    }

    /**
     * Create template from the given model
     *
     * @param Model\Template $templateModel
     *
     * @return static
     */
    public static function fromModel(Model\Template $templateModel): self
    {
        $template = new static();

        $templateModel->settings = json_decode($templateModel->settings, true);

        $coverPage = (new CoverPage())
            ->setColor($templateModel->settings['color'])
            ->setTitle($templateModel->settings['title']);

        if (isset($templateModel->settings['cover_page_background_image'])) {
            $coverPage->setBackgroundImage($templateModel->settings['cover_page_background_image']);
        }

        if (isset($templateModel->settings['cover_page_logo'])) {
            $coverPage->setLogo($templateModel->settings['cover_page_logo']);
        }

        $template
            ->setCoverPage($coverPage)
            ->setHeader(new HeaderOrFooter(HeaderOrFooter::HEADER, $templateModel->settings))
            ->setFooter(new HeaderOrFooter(HeaderOrFooter::FOOTER, $templateModel->settings));

        return $template;
    }

    /**
     * @return CoverPage
     */
    public function getCoverPage()
    {
        return $this->coverPage;
    }

    /**
     * @param CoverPage $coverPage
     *
     * @return $this
     */
    public function setCoverPage(CoverPage $coverPage)
    {
        $this->coverPage = $coverPage;

        return $this;
    }

    /**
     * @return HeaderOrFooter
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param HeaderOrFooter $header
     *
     * @return $this
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return HeaderOrFooter
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param HeaderOrFooter $footer
     *
     * @return $this
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param mixed $preview
     *
     * @return $this
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    protected function assemble()
    {
        if ($this->preview) {
            $this->getAttributes()->add('class', 'preview');
        }

        $this->add($this->getCoverPage()->setMacros($this->macros));

//        $page = Html::tag(
//            'div',
//            ['class' => 'main'],
//            Html::tag('div', ['class' => 'page-content'], [
//                $this->header->setMacros($this->macros),
//                Html::tag(
//                    'div',
//                    [
//                        'class' => 'main'
//                    ]
//                ),
//                $this->footer->setMacros($this->macros)
//            ])
//        );
//
//        $this->add($page);
    }
}
