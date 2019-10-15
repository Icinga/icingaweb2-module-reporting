<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Widget;

use Icinga\Module\Reporting\Common\Macros;
use Icinga\Module\Reporting\Database;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Sql\Select;

class Template extends BaseHtmlElement
{
    use Database;
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

    public static function fromDb($id)
    {
        $template = new static();

        $select = (new Select())
            ->from('template')
            ->columns('*')
            ->where(['id = ?' => $id]);

        $row = $template->getDb()->select($select)->fetch();

        if ($row === false) {
            return null;
        }

        $row->settings = json_decode($row->settings, true);

        $coverPage = (new CoverPage())
            ->setColor($row->settings['color'])
            ->setTitle($row->settings['title']);

        if (isset($row->settings['cover_page_background_image'])) {
            $coverPage->setBackgroundImage($row->settings['cover_page_background_image']);
        }

        if (isset($row->settings['cover_page_logo'])) {
            $coverPage->setLogo($row->settings['cover_page_logo']);
        }

        $template
            ->setCoverPage($coverPage)
            ->setHeader(new HeaderOrFooter(HeaderOrFooter::HEADER, $row->settings))
            ->setFooter(new HeaderOrFooter(HeaderOrFooter::FOOTER, $row->settings));

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
