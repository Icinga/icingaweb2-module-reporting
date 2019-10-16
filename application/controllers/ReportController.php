<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Application\Hook;
use Icinga\Module\Pdfexport\ProvidedHook\Pdfexport;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\ReportForm;
use Icinga\Module\Reporting\Web\Forms\ScheduleForm;
use Icinga\Module\Reporting\Web\Forms\SendForm;
use Icinga\Web\StyleSheet;
use ipl\Html\Error;
use ipl\Html\Html;
use ipl\Html\HtmlString;
use reportingipl\Web\Url;
use reportingipl\Web\Widget\ActionBar;
use reportingipl\Web\Widget\DropdownLink;

class ReportController extends Controller
{
    use Database;

    /** @var Report */
    protected $report;

    public function init()
    {
        $this->report = Report::fromDb($this->params->getRequired('id'));
    }

    public function indexAction()
    {
        $this->setTitle($this->report->getName());

        $this->addControl($this->assembleActions());

        try {
            $this->addContent($this->report->toHtml());
        } catch (\Exception $e) {
            $this->addContent(Error::show($e));
        }
    }

    public function editAction()
    {
        $this->setTitle('Edit Report');

        $values = [
            'name'      => $this->report->getName(),
            // TODO(el): Must cast to string here because ipl/html does not support integer return values for attribute callbacks
            'timeframe' => (string) $this->report->getTimeframe()->getId(),
        ];

        $reportlet = $this->report->getReportlets()[0];

        $values['reportlet'] = $reportlet->getClass();

        foreach ($reportlet->getConfig() as $name => $value) {
            $values[$name] = $value;
        }

        $form = new ReportForm();
        $form->setId($this->report->getId());
        $form->populate($values);
        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/reports');

        $this->addContent($form);
    }

    public function sendAction()
    {
        $this->setTitle('Send Report');

        $form = new SendForm();
        $form
            ->setReport($this->report)
            ->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, "reporting/report?id={$this->report->getId()}");

        $this->addContent($form);
    }

    public function scheduleAction()
    {
        $this->setTitle('Schedule');

        $form = new ScheduleForm();
        $form
            ->setReport($this->report)
            ->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, "reporting/report?id={$this->report->getId()}");

        $this->addContent($form);
    }

    public function downloadAction()
    {
        $type = $this->params->getRequired('type');

        $name = sprintf(
            '%s (%s) %s',
            $this->report->getName(),
            $this->report->getTimeframe()->getName(),
            date('Y-m-d H:i')
        );

        switch ($type) {
            case 'pdf':
                /** @var Hook\PdfexportHook */
                Pdfexport::first()->streamPdfFromHtml($this->report->toPdf(), $name);
                exit;
            case 'csv':
                $response = $this->getResponse();
                $response
                    ->setHeader('Content-Type', 'text/csv')
                    ->setHeader('Cache-Control', 'no-store')
                    ->setHeader(
                        'Content-Disposition',
                        'attachment; filename=' . $name . '.csv'
                    )
                    ->appendBody($this->report->toCsv())
                    ->sendResponse();
                exit;
            case 'json':
                $response = $this->getResponse();
                $response
                    ->setHeader('Content-Type', 'application/json')
                    ->setHeader('Cache-Control', 'no-store')
                    ->setHeader(
                        'Content-Disposition',
                        'inline; filename=' . $name . '.json'
                    )
                    ->appendBody($this->report->toJson())
                    ->sendResponse();
                exit;
        }
    }

    protected function assembleActions()
    {
        $reportId = $this->report->getId();

        $download = (new DropdownLink('Download'))
            ->addLink('PDF', Url::fromPath('reporting/report/download?type=pdf', ['id' => $reportId]));

        if ($this->report->providesData()) {
            $download->addLink('CSV', Url::fromPath('reporting/report/download?type=csv', ['id' => $reportId]));
            $download->addLink('JSON', Url::fromPath('reporting/report/download?type=json', ['id' => $reportId]));
        }

        $actions = new ActionBar();

        $actions
            ->addLink('Modify', Url::fromPath('reporting/report/edit', ['id' => $reportId]), 'edit')
            ->addLink('Schedule', Url::fromPath('reporting/report/schedule', ['id' => $reportId]), 'calendar-empty')
            ->add($download)
            ->addLink('Send', Url::fromPath('reporting/report/send', ['id' => $reportId]), 'forward');

        return $actions;
    }
}
