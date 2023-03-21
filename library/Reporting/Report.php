<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use DateTime;
use Exception;
use Icinga\Module\Pdfexport\PrintableHtmlDocument;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Web\Widget\Template;
use ipl\Html\HtmlDocument;

class Report
{
    use Database;

    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $author;

    /** @var Timeframe */
    protected $timeframe;

    /** @var Reportlet[] */
    protected $reportlets;

    /** @var Schedule */
    protected $schedule;

    /** @var Template */
    protected $template;

    /**
     * Create report from the given model
     *
     * @param Model\Report $reportModel
     *
     * @return static
     * @throws Exception If no reportlets are configured
     */
    public static function fromModel(Model\Report $reportModel): self
    {
        $report = new static();

        $report->id = $reportModel->id;
        $report->name = $reportModel->name;
        $report->author = $reportModel->author;
        $report->timeframe = Timeframe::fromModel($reportModel->timeframe);

        $template = $reportModel->template->first();
        if ($template !== null) {
            $report->template = Template::fromModel($template);
        }

        $reportlets = [];
        foreach ($reportModel->reportlets as $reportlet) {
            $reportlet->report_name = $reportModel->name;
            $reportlet->report_id = $reportModel->id;
            $reportlets[] = Reportlet::fromModel($reportlet);
        }

        if (empty($reportlets)) {
            throw new Exception('No reportlets configured');
        }

        $report->reportlets = $reportlets;

        $schedule = $reportModel->schedule->first();
        if ($schedule !== null) {
            $report->schedule = Schedule::fromModel($schedule, $report);
        }

        return $report;
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
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return  Timeframe
     */
    public function getTimeframe()
    {
        return $this->timeframe;
    }

    /**
     * @return  Reportlet[]
     */
    public function getReportlets()
    {
        return $this->reportlets;
    }

    /**
     * @return  Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function providesData()
    {
        foreach ($this->getReportlets() as $reportlet) {
            $implementation = $reportlet->getImplementation();

            if ($implementation->providesData()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return  HtmlDocument
     */
    public function toHtml()
    {
        $timerange = $this->getTimeframe()->getTimerange();

        $html = new HtmlDocument();

        foreach ($this->getReportlets() as $reportlet) {
            $implementation = $reportlet->getImplementation();

            $html->add($implementation->getHtml($timerange, $reportlet->getConfig()));
        }

        return $html;
    }

    /**
     * @return  string
     */
    public function toCsv()
    {
        $timerange = $this->getTimeframe()->getTimerange();
        $convertFloats = version_compare(PHP_VERSION, '8.0.0', '<');

        $csv = [];

        foreach ($this->getReportlets() as $reportlet) {
            $implementation = $reportlet->getImplementation();

            if ($implementation->providesData()) {
                $data = $implementation->getData($timerange, $reportlet->getConfig());
                $csv[] = array_merge($data->getDimensions(), $data->getValues());
                foreach ($data->getRows() as $row) {
                    $values = $row->getValues();
                    if ($convertFloats) {
                        foreach ($values as &$value) {
                            if (is_float($value)) {
                                $value = sprintf('%.4F', $value);
                            }
                        }
                    }

                    $csv[] = array_merge($row->getDimensions(), $values);
                }

                break;
            }
        }

        return Str::putcsv($csv);
    }

    /**
     * @return  string
     */
    public function toJson()
    {
        $timerange = $this->getTimeframe()->getTimerange();

        $json = [];

        foreach ($this->getReportlets() as $reportlet) {
            $implementation = $reportlet->getImplementation();

            if ($implementation->providesData()) {
                $data = $implementation->getData($timerange, $reportlet->getConfig());
                $dimensions = $data->getDimensions();
                $values = $data->getValues();
                foreach ($data->getRows() as $row) {
                    $json[] = \array_combine($dimensions, $row->getDimensions())
                        + \array_combine($values, $row->getValues());
                }

                break;
            }
        }

        return json_encode($json);
    }

    /**
     * @return PrintableHtmlDocument
     *
     * @throws Exception
     */
    public function toPdf()
    {
        $html = (new PrintableHtmlDocument())
            ->setTitle($this->getName())
            ->addAttributes(['class' => 'icinga-module module-reporting'])
            ->addHtml($this->toHtml());

        if ($this->template !== null) {
            $this->template->setMacros([
                'title'               => $this->name,
                'date'                => (new DateTime())->format('jS M, Y'),
                'time_frame'          => $this->timeframe->getName(),
                'time_frame_absolute' => sprintf(
                    'From %s to %s',
                    $this->timeframe->getTimerange()->getStart()->format('r'),
                    $this->timeframe->getTimerange()->getEnd()->format('r')
                )
            ]);

            $html->setCoverPage($this->template->getCoverPage()->setMacros($this->template->getMacros()));
            $html->setHeader($this->template->getHeader()->setMacros($this->template->getMacros()));
            $html->setFooter($this->template->getFooter()->setMacros($this->template->getMacros()));
        }

        return $html;
    }
}
