<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

class Reportlet
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $class;

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
     * @return  string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param   string  $class
     *
     * @return  $this
     */
    public function setClass($class)
    {
        $this->class = $class;

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
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return  \Icinga\Module\Reporting\Hook\ReportHook
     */
    public function getImplementation()
    {
        $class = $this->getClass();

        return new $class;
    }
}
