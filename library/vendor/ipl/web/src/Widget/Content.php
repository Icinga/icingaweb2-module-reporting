<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Widget;

use ipl\Html\BaseHtmlElement;

class Content extends BaseHtmlElement
{
    protected $tag = 'div';

    protected $contentSeparator = "\n";

    protected $defaultAttributes = ['class' => 'content'];
}
