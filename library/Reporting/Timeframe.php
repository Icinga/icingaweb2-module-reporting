<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Icinga\Module\Reporting\Model;

class Timeframe
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $title;

    /** @var string */
    protected $start;

    /** @var string */
    protected $end;

    /**
     * Create timeframe from the given model
     *
     * @param Model\Timeframe $timeframeModel
     *
     * @return static
     */
    public static function fromModel(Model\Timeframe $timeframeModel): self
    {
        $timeframe = new static();

        $timeframe->id = $timeframeModel->id;
        $timeframe->name = $timeframeModel->name;
        $timeframe->title = $timeframeModel->title;
        $timeframe->start = $timeframeModel->start;
        $timeframe->end = $timeframeModel->end;

        return $timeframe;
    }

    /**
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return  string
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return  string
     */
    public function getEnd()
    {
        return $this->end;
    }

    public function getTimerange()
    {
        $start = new \DateTime($this->getStart());
        $end = new \DateTime($this->getEnd());

        return new Timerange($start, $end);
    }
}
