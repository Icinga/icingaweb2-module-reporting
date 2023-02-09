<?php

namespace Icinga\Module\Reporting;

use Countable;
use DateTimeInterface;

/**
 * Represents a single sla timeline for a given timeframe
 */
class SlaTimeline implements Countable
{
    /** @var DateTimeInterface Start time of the generated report timeframe */
    protected $start;

    /** @var DateTimeInterface End time of the generated report timeframe */
    protected $end;

    /** @var array Sla event time history within a given timeframe */
    protected $time = [];

    /** @var array Sla event type within a given timeframe */
    protected $event = [];

    /** @var array Sla history hard_state within a given timeframe */
    protected $state = [];

    /** @var array Sla history previous hard_state within a given timeframe */
    protected $previousState = [];

    /** @var int Sum of the problem time of this timeline */
    protected $problemTime = 0;

    /** @var int Total time of this timeline */
    protected $totalTime = 0;

    /** @var int The initial hard state of this timeline */
    protected $initialHardState = 0;

    public function __construct(DateTimeInterface $start, DateTimeInterface $end)
    {
        $this->start = clone $start;
        $this->end = clone $end;
    }

    /**
     * Set the initial hard state of this timeline
     *
     * @param int $state
     *
     * @return $this
     */
    public function setInitialHardState(int $state): self
    {
        $this->initialHardState = $state;

        return $this;
    }

    /**
     * Get the calculated SLA result of this timeline
     *
     * @return float
     */
    public function getResult(): float
    {
        $problemTime = 0;
        $activeDowntimes = 0;
        $lastEventTime = (int) $this->start->format('Uv');
        $totalTime = (int) $this->end->format('Uv') - $lastEventTime;

        $lastHardState = $this->initialHardState;
        $count = $this->count();
        for ($i = 0; $i < $count; ++$i) {
            $event = $this->event[$i];
            $time = $this->time[$i];
            $state = $this->state[$i];

            if ($this->previousState[$i] === 99 || ($lastHardState === 99 && $event !== 'state_change')) {
                $totalTime -= $time - $lastEventTime;
            } elseif ($lastHardState > 0 && $lastHardState !== 99 && $activeDowntimes === 0) {
                $problemTime += $time - $lastEventTime;
            }

            $lastEventTime = $time;
            if ($event === 'state_change') {
                $lastHardState = $state;
            } elseif ($event === 'downtime_start') {
                ++$activeDowntimes;
            } elseif ($event === 'downtime_end') {
                --$activeDowntimes;
            }
        }

        $this->problemTime = $problemTime;
        $this->totalTime = $totalTime;

        return 100 * ($totalTime - $problemTime) / $totalTime;
    }

    /**
     * Add event time to this timeline
     *
     * @param int $time
     *
     * @return $this
     */
    public function addTime(int $time): self
    {
        $this->time[] = $time;

        return $this;
    }

    /**
     * Add event type to this timeline
     *
     * @param string $event
     *
     * @return $this
     */
    public function addEvent(string $event): self
    {
        $this->event[] = $event;

        return $this;
    }

    /**
     * Add hard state to this timeline
     *
     * @param ?int $state
     *
     * @return $this
     */
    public function addState(?int $state): self
    {
        $this->state[] = $state === null ? null : $state;

        return $this;
    }

    /**
     * Add previous hard state to this timeline
     *
     * @param ?int $previousState
     *
     * @return $this
     */
    public function addPreviousState(?int $previousState): self
    {
        $this->previousState[] = $previousState === null ? null : $previousState;

        return $this;
    }

    /**
     * Get the problem time of this timeline
     *
     * @return int
     */
    public function getProblemTime(): int
    {
        return $this->problemTime;
    }

    /**
     * Get the total time of this timeline
     *
     * @return int
     */
    public function getTotalTime(): int
    {
        return $this->totalTime;
    }

    public function count(): int
    {
        return count($this->time);
    }

    public function __toString()
    {
        $timeline = '';
        for ($i = 0; $i < $this->count(); ++$i) {
            $timeline .= 'time: ' . $this->time[$i] . ' | event: ' . $this->event[$i] . ' | hard_state: ';
            if (isset($this->state[$i])) {
                $timeline .= $this->state[$i];
            }

            $timeline .=  '| previous_hard_state: ' . $this->previousState[$i];

            $timeline .= PHP_EOL;
        }

        return $timeline;
    }
}
