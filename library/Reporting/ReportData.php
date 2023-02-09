<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Module\Reporting\Common\SlaTimelines;

class ReportData implements \Countable
{
    use Dimensions;
    use Values;
    use SlaTimelines;

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

    public function getIcingaDBAvg()
    {
        $totals = 0.0;
        $count = 0;
        foreach ($this->getAllTimelines() as $name => $timelines) {
            $totalTime = 0;
            $problemTime = 0;

            /** @var SlaTimeline $timeline */
            foreach ($timelines as $timeline) {
                $totalTime += $timeline->getTotalTime();
                $problemTime += $timeline->getProblemTime();
            }

            ++$count;
            $totals += 100 * ($totalTime - $problemTime) / $totalTime;
        }

        return $totals / $count;
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
