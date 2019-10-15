<?php

namespace Icinga\Module\Reporting\Web\Widget;

use Icinga\Module\Reporting\Common\Macros;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

class HeaderOrFooter extends BaseHtmlElement
{
    use Macros;

    const HEADER = 'header';

    const FOOTER = 'footer';

    protected $type;

    protected $data;

    protected $tag = 'div';

    public function __construct($type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    protected function resolveVariable($variable)
    {
        switch ($variable) {
            case 'report_title':
                $resolved = Html::tag('span', ['class' => 'title']);
                break;
            case 'time_frame':
                $resolved = Html::tag('p', $this->getMacro('time_frame'));
                break;
            case 'page_number':
                $resolved = Html::tag('span', ['class' => 'pageNumber']);
                break;
            case 'total_number_of_pages':
                $resolved = Html::tag('span', ['class' => 'totalPages']);
                break;
            case 'page_of':
                $resolved = Html::tag('p', Html::sprintf(
                    '%s / %s',
                    Html::tag('span', ['class' => 'pageNumber']),
                    Html::tag('span', ['class' => 'totalPages'])
                ));
                break;
            case 'date':
                $resolved = Html::tag('span', ['class' => 'date']);
                break;
            default:
                $resolved = $variable;
                break;
        }

        return $resolved;
    }

    protected function createColumn(array $data, $key)
    {
        $typeKey = "${key}_type";
        $valueKey = "${key}_value";
        $type = isset($data[$typeKey]) ? $data[$typeKey] : null;

        switch ($type) {
            case 'text':
                $column = Html::tag('p', $data[$valueKey]);
                break;
            case 'image':
                $column = Html::tag('img', ['height' => 13, 'src' => Template::getDataUrl($data[$valueKey])]);
                break;
            case 'variable':
                $column = $this->resolveVariable($data[$valueKey]);
                break;
            default:
                $column = Html::tag('div');
                break;
        }

        return $column;
    }

    protected function assemble()
    {
        $this->getAttributes()->add('class', $this->type);

        for ($i = 1; $i <= 3; ++$i) {
            $this->add($this->createColumn($this->data, "{$this->type}_column{$i}"));
        }
    }
}
