<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

class Schedule
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $reportId;

    /** @var \DateTime */
    protected $start;

    /** @var string */
    protected $frequency;

    /** @var string */
    protected $action;

    /** @var array */
    protected $config;

    /** @var Report */
    protected $report;

    /**
     * Create schedule from the given model
     *
     * @param Model\Schedule $scheduleModel
     *
     * @return static
     */

    public static function fromModel(Model\Schedule $scheduleModel): self
    {
        $schedule = new static();

        $schedule->id = $scheduleModel->id;
        $schedule->reportId = $scheduleModel->report_id;
        $schedule->start = $scheduleModel->start;
        $schedule->frequency = $scheduleModel->frequency;
        $schedule->action = $scheduleModel->action;

        if ($scheduleModel->config) {
            $schedule->config = json_decode($scheduleModel->config, true);
        }

        return $schedule;
    }

    /**
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getReportId()
    {
        return $this->reportId;
    }

    /**
     * @return  \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return  string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @return  string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the report this schedule belongs to
     *
     * @return Report
     */
    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * Set the report this schedule belongs to
     *
     * @param Report $report
     *
     * @return $this
     */
    public function setReport(Report $report): self
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return  string
     */
    public function getChecksum()
    {
        return \md5(
            $this->getId()
            . $this->getReportId()
            . $this->getStart()->format('Y-m-d H:i:s')
            . $this->getAction()
            . $this->getFrequency()
            . \json_encode($this->getConfig())
        );
    }
}
