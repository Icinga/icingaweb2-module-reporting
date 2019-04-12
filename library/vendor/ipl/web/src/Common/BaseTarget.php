<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Common;

/**
 * @method \ipl\Html\Attributes getAttributes()
 */
trait BaseTarget
{
    /**
     * @return  string|null
     */
    public function getBaseTarget()
    {
        return $this->getAttributes()->get('data-base-target')->getValue();
    }

    /**
     * @param   string  $target
     *
     * @return  $this
     */
    public function setBaseTarget($target)
    {
        $this->getAttributes()->set('data-base-target', $target);

        return $this;
    }
}
