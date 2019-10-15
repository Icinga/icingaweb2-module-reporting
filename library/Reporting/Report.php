<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use DateTime;
use Exception;
use Icinga\Module\Pdfexport\PrintableHtmlDocument;
use Icinga\Module\Reporting\Web\Widget\Template;
use ipl\Html\Html;
use ipl\Html\HtmlDocument;
use ipl\Html\HtmlString;
use ipl\Sql;

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
     * @param   int $id
     *
     * @return  static
     *
     * @throws  Exception
     */
    public static function fromDb($id)
    {
        $report = new static();

        $db = $report->getDb();

        $select = (new Sql\Select())
            ->from('report')
            ->columns('*')
            ->where(['id = ?' => $id]);

        $row = $db->select($select)->fetch();

        if ($row === false) {
            throw new Exception('Report not found');
        }

        $report
            ->setId($row->id)
            ->setName($row->name)
            ->setAuthor($row->author)
            ->setTimeframe(Timeframe::fromDb($row->timeframe_id))
            ->setTemplate(Template::fromDb($row->template_id));

        $select = (new Sql\Select())
            ->from('reportlet')
            ->columns('*')
            ->where(['report_id = ?' => $id]);

        $row = $db->select($select)->fetch();

        if ($row === false) {
            throw new Exception('No reportlets configured.');
        }

        $reportlet = new Reportlet();

        $reportlet
            ->setId($row->id)
            ->setClass($row->class);

        $select = (new Sql\Select())
            ->from('config')
            ->columns('*')
            ->where(['reportlet_id = ?' => $row->id]);

        $rows = $db->select($select)->fetchAll();

        $config = [];

        foreach ($rows as $row) {
            $config[$row->name] = $row->value;
        }

        $reportlet->setConfig($config);

        $report->setReportlets([$reportlet]);

        $select = (new Sql\Select())
            ->from('schedule')
            ->columns('*')
            ->where(['report_id = ?' => $id]);

        $row = $db->select($select)->fetch();

        if ($row !== false) {
            $schedule = new Schedule();

            $schedule
                ->setId($row->id)
                ->setStart((new \DateTime())->setTimestamp((int) $row->start / 1000))
                ->setFrequency($row->frequency)
                ->setAction($row->action)
                ->setConfig(json_decode($row->config, true));

            $report->setSchedule($schedule);
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param   string  $name
     *
     * @return  $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return  string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param   string  $author
     *
     * @return  $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return  Timeframe
     */
    public function getTimeframe()
    {
        return $this->timeframe;
    }

    /**
     * @param   Timeframe   $timeframe
     *
     * @return  $this
     */
    public function setTimeframe(Timeframe $timeframe)
    {
        $this->timeframe = $timeframe;

        return $this;
    }

    /**
     * @return  Reportlet[]
     */
    public function getReportlets()
    {
        return $this->reportlets;
    }

    /**
     * @param   Reportlet[] $reportlets
     *
     * @return  $this
     */
    public function setReportlets(array $reportlets)
    {
        $this->reportlets = $reportlets;

        return $this;
    }

    /**
     * @return  Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param   Schedule    $schedule
     *
     * @return  $this
     */
    public function setSchedule(Schedule $schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param Template $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
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

        $csv = [];

        foreach ($this->getReportlets() as $reportlet) {
            $implementation = $reportlet->getImplementation();

            if ($implementation->providesData()) {
                $data = $implementation->getData($timerange, $reportlet->getConfig());
                $csv[] = array_merge($data->getDimensions(), $data->getValues());
                foreach ($data->getRows() as $row) {
                    $csv[] = array_merge($row->getDimensions(), $row->getValues());
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
        $style = <<<'STYLE'
<style type="text/css">
@font-face {
    font-family: "Helvetica Neue", "Helvetica", "Arial", sans-serif;
}

.header,
.footer {
    display: flex;
    justify-content: space-between;

    font-size: 8px;
    margin-left: 0.75cm;
    margin-right: 0.75cm;
    width: 100%;

    > * {
        margin-left: .25em;
        margin-right: .25em;
    }
}

p {
    margin: 0;
}
</style>
STYLE;

        $html = (new PrintableHtmlDocument())
            ->setTitle($this->getName())
            ->addAttributes(['class' => 'icinga-module module-reporting'])
            ->add(new HtmlString($this->toHtml()));

        if ($this->template !== null) {
            $this->template->setMacros([
                'date'       => (new DateTime())->format('jS M, Y'),
                'time_frame' => $this->timeframe->getName(),
                'title'      => $this->name
            ]);

            $html->setCoverPage($this->template->getCoverPage()->setMacros($this->template->getMacros()));

            $header = $this->template->getHeader()->setMacros($this->template->getMacros());
            $html->setHeader(new HtmlString(
                $style
                . $header->render()
            ));

            $footer = $this->template->getFooter()->setMacros($this->template->getMacros());
            $html->setFooter(new HtmlString(
                $style
                . $footer->render()
            ));
        }

        return $html;
    }
}
