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

    /**
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param   int $id
     *
     * @return  $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return  int
     */
    public function getReportId()
    {
        return $this->reportId;
    }

    /**
     * @param   int $id
     *
     * @return  $this
     */
    public function setReportId($id)
    {
        $this->reportId = $id;

        return $this;
    }

    /**
     * @return  \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param   \DateTime  $start
     *
     * @return  $this
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return  string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param   string  $frequency
     *
     * @return  $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return  string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param   string  $action
     *
     * @return  $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return  array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param   array   $config
     *
     * @return  $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

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
            .  $this->getAction()
            . $this->getFrequency()
            . \json_encode($this->getConfig())
        );
    }
}
