<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting;

class Timerange
{
    /** @var \DateTime */
    protected $start;

    /** @var \DateTime */
    protected $end;

    public function __construct(\DateTime $start, \DateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return  \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return  \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }
}
