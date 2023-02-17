<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

class Reportlet
{
    /** @var string */
    protected $class;

    /** @var array */
    protected $config;

    /**
     * Create reportlet from the given model
     *
     * @param Model\Reportlet $reportletModel
     *
     * @return static
     */
    public static function fromModel(Model\Reportlet $reportletModel): self
    {
        $reportlet = new static();

        $reportlet->id = $reportletModel->id;
        $reportlet->class = $reportletModel->class;

        $reportletConfig = [
            'name' => $reportletModel->report_name,
            'id'   => $reportletModel->report_id
        ];

        foreach ($reportletModel->config as $config) {
            $reportletConfig[$config->name] = $config->value;
        }

        $reportlet->config = $reportletConfig;

        return $reportlet;
    }

    /**
     * @return  string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return  array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return  \Icinga\Module\Reporting\Hook\ReportHook
     */
    public function getImplementation()
    {
        $class = $this->getClass();

        return new $class();
    }
}
