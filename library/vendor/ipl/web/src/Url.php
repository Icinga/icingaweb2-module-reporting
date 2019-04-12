<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web;

/**
 * @TODO(el): Don't depend on Icinga Web's Url
 */
class Url extends \Icinga\Web\Url
{
    public function __toString()
    {
        return $this->getAbsoluteUrl('&');
    }
}
