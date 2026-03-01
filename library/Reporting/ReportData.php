<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting;

class ReportData implements \Countable
{
    use Dimensions;
    use Values;

    /** @var ReportRow[]|null */
    protected $rows;

    public function getRows()
    {
        return $this->rows;
    }

    public function setRows(array $rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public function getAverages()
    {
        $totals = $this->getTotals();
        $averages = [];
        $count = \count($this);

        foreach ($totals as $total) {
            $averages[] = $total / $count;
        }

        return $averages;
    }

//    public function getMaximums()
//    {
//    }

//    public function getMinimums()
//    {
//    }

    public function getTotals()
    {
        $totals = [];

        foreach ((array) $this->getRows() as $row) {
            $i = 0;
            foreach ((array) $row->getValues() as $value) {
                if (! isset($totals[$i])) {
                    $totals[$i] = $value;
                } else {
                    $totals[$i] += $value;
                }

                ++$i;
            }
        }

        return $totals;
    }

    public function count(): int
    {
        return count((array) $this->getRows());
    }
}
