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
use Icinga\Module\Reporting\Web\Widget\CompatDropdown;
use ipl\Html\Error;
use ipl\Html\Form;
use ipl\Web\Url;
use ipl\Web\Widget\ActionBar;
use Icinga\Util\Environment;

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
        $this->addTitleTab($this->report->getName());

        $this->addControl($this->assembleActions());

        Environment::raiseExecutionTime();
        Environment::raiseMemoryLimit();

        try {
            $this->addContent($this->report->toHtml());
        } catch (\Exception $e) {
            $this->addContent(Error::show($e));
        }
    }

    public function editAction()
    {
        $this->assertPermission('reporting/reports');
        $this->addTitleTab($this->translate('Edit Report'));

        $values = [
            'name'      => $this->report->getName(),
            // TODO(el): Must cast to string here because ipl/html does not
            //           support integer return values for attribute callbacks
            'timeframe' => (string) $this->report->getTimeframe()->getId(),
        ];

        foreach ($this->report->getReportlets() as $key => $reportlet) {
            $values['reportlet'][$key + 1]['__class'] = $reportlet->getClass();

            foreach ($reportlet->getConfig() as $name => $value) {
                $values['reportlet'][$key + 1][$name] = $value;
            }
        }

        $form = ReportForm::fromId($this->report->getId())
            ->on(ReportForm::ON_SUCCESS, function () {
                $this->redirectNow('reporting/reports');
            })
            ->populate($values)
            ->handleRequest(ServerRequest::fromGlobals());

        $this->addContent($form);
    }

    public function sendAction()
    {
        $this->addTitleTab($this->translate('Send Report'));

        Environment::raiseExecutionTime();
        Environment::raiseMemoryLimit();

        $form = (new SendForm())
            ->setReport($this->report)
            ->on(SendForm::ON_SUCCESS, function () {
                $this->redirectNow("reporting/report?id={$this->report->getId()}");
            })
            ->handleRequest(ServerRequest::fromGlobals());

        $this->addContent($form);
    }

    public function scheduleAction()
    {
        $this->assertPermission('reporting/schedules');
        $this->addTitleTab($this->translate('Schedule'));

        $form = ScheduleForm::fromReport($this->report)
            ->on(ScheduleForm::ON_SUCCESS, function () {
                $this->redirectNow("reporting/report?id={$this->report->getId()}");
            })
            ->handleRequest(ServerRequest::fromGlobals());

        $this->addContent($form);
    }

    public function downloadAction()
    {
        $type = $this->params->getRequired('type');

        Environment::raiseExecutionTime();
        Environment::raiseMemoryLimit();

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

        $download = (new CompatDropdown('Download'))
            ->addLink(
                'PDF',
                Url::fromPath('reporting/report/download?type=pdf', ['id' => $reportId]),
                null,
                ['target' => '_blank']
            );

        if ($this->report->providesData()) {
            $download->addLink(
                'CSV',
                Url::fromPath('reporting/report/download?type=csv', ['id' => $reportId]),
                null,
                ['target' => '_blank']
            );
            $download->addLink(
                'JSON',
                Url::fromPath('reporting/report/download?type=json', ['id' => $reportId]),
                null,
                ['target' => '_blank']
            );
        }

        $actions = new ActionBar();

        if ($this->hasPermission('reporting/reports')) {
            $actions->addLink(
                'Modify',
                Url::fromPath('reporting/report/edit', ['id' => $reportId]),
                'edit'
            );
        }

        if ($this->hasPermission('reporting/schedules')) {
            $actions->addLink(
                'Schedule',
                Url::fromPath('reporting/report/schedule', ['id' => $reportId]),
                'calendar-empty'
            );
        }

        $actions
            ->add($download)
            ->addLink('Send', Url::fromPath('reporting/report/send', ['id' => $reportId]), 'forward');

        return $actions;
    }
}
