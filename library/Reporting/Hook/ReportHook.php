<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Hook;

use Icinga\Application\ClassLoader;
use Icinga\Application\Hook;
use Icinga\Module\Reporting\ReportData;
use Icinga\Module\Reporting\Timerange;
use ipl\Html\Form;
use ipl\Html\ValidHtml;

abstract class ReportHook
{
    /**
     * Get the name of the report
     *
     * @return  string
     */
    abstract public function getName();

    /**
     * @param   Timerange   $timerange
     * @param   array       $config
     *
     * @return  ReportData|null
     */
    public function getData(Timerange $timerange, array $config = null)
    {
        return null;
    }

    /**
     * Get the HTML of the report
     *
     * @param   Timerange   $timerange
     * @param   array       $config
     *
     * @return  ValidHtml|null
     */
    public function getHtml(Timerange $timerange, array $config = null)
    {
        return null;
    }

    /**
     * Initialize the report's configuration form
     *
     * @param   Form    $form
     */
    public function initConfigForm(Form $form)
    {
    }

    /**
     * Get the description of the report
     *
     * @return  string
     */
    public function getDescription()
    {
        return null;
    }

    /**
     * Get whether the report provides reporting data
     *
     * @return  bool
     */
    public function providesData()
    {
        try {
            $method = new \ReflectionMethod($this, 'getData');
        } catch (\ReflectionException $e) {
            return false;
        }

        return $method->getDeclaringClass()->getName() !== self::class;
    }

    /**
     * Get whether the report provides HTML
     *
     * @return  bool
     */
    public function providesHtml()
    {
        try {
            $method = new \ReflectionMethod($this, 'getHtml');
        } catch (\ReflectionException $e) {
            return false;
        }

        return $method->getDeclaringClass()->getName() !== self::class;
    }

    /**
     * Get the module name of the report
     *
     * @return  string
     */
    final public function getModuleName()
    {
        return ClassLoader::extractModuleName(get_class($this));
    }

    /**
     * Get all provided reports
     *
     * @return  ReportHook[]
     */
    final public static function getReports()
    {
        return Hook::all('reporting/Report');
    }
}
