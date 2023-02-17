<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Application\Hook;
use Icinga\Module\Pdfexport\ProvidedHook\Pdfexport;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\ReportForm;
use Icinga\Module\Reporting\Web\Forms\ScheduleForm;
use Icinga\Module\Reporting\Web\Forms\SendForm;
use Icinga\Module\Reporting\Web\Widget\CompatDropdown;
use ipl\Html\Error;
use ipl\Stdlib\Filter;
use ipl\Web\Url;
use ipl\Web\Widget\ActionBar;
use Icinga\Util\Environment;
use ipl\Web\Widget\ActionLink;

class ReportController extends Controller
{
    use Database;

    /** @var Report */
    protected $report;

    public function init()
    {
        $reportId = $this->params->getRequired('id');

        $report = Model\Report::on($this->getDb())
            ->with(['timeframe'])
            ->filter(Filter::equal('id', $reportId))
            ->first();

        if ($report === null) {
            $this->httpNotFound($this->translate('Report not found'));
        }

        $this->report = Report::fromModel($report);
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

    public function cloneAction()
    {
        $this->assertPermission('reporting/reports');
        $this->addTitleTab($this->translate('Clone Report'));

        $values = ['timeframe' => (string) $this->report->getTimeframe()->getId()];

        $reportlet = $this->report->getReportlets()[0];

        $values['reportlet'] = $reportlet->getClass();

        foreach ($reportlet->getConfig() as $name => $value) {
            if ($name === 'name') {
                if (preg_match('/(?:Clone )(\d+)$/', $value, $matches)) {
                    $value = preg_replace('/\d+$/', ++$matches[1], $value);
                } else {
                    $value .= ' Clone 1';
                }
            }

            $values[$name] = $value;
        }

        $form = (new ReportForm())
            ->setSubmitButtonLabel($this->translate('Clone Report'))
            ->setAction((string) Url::fromRequest())
            ->populate($values)
            ->on(ReportForm::ON_SUCCESS, function () {
                $this->redirectNow('__CLOSE__');
            })
            ->handleRequest($this->getServerRequest());

        $this->addContent($form);
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

        $reportlet = $this->report->getReportlets()[0];

        $values['reportlet'] = $reportlet->getClass();

        foreach ($reportlet->getConfig() as $name => $value) {
            $values[$name] = $value;
        }

        $form = ReportForm::fromId($this->report->getId())
            ->setAction((string) Url::fromRequest())
            ->populate($values)
            ->on(ReportForm::ON_SUCCESS, function () {
                $this->redirectNow('__CLOSE__');
            })
            ->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }

    public function sendAction()
    {
        $this->addTitleTab($this->translate('Send Report'));

        Environment::raiseExecutionTime();
        Environment::raiseMemoryLimit();

        $form = (new SendForm())
            ->setReport($this->report)
            ->setAction((string) Url::fromRequest())
            ->on(SendForm::ON_SUCCESS, function () {
                $this->redirectNow("reporting/report?id={$this->report->getId()}");
            })
            ->handleRequest($this->getServerRequest());

        $this->addContent($form);
    }

    public function scheduleAction()
    {
        $this->assertPermission('reporting/schedules');
        $this->addTitleTab($this->translate('Schedule'));

        $form = ScheduleForm::fromReport($this->report)
            ->setAction((string) Url::fromRequest())
            ->on(ScheduleForm::ON_SUCCESS, function () {
                $this->redirectNow("reporting/report?id={$this->report->getId()}");
            })
            ->handleRequest($this->getServerRequest());

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
            $actions->addHtml(
                new ActionLink(
                    $this->translate('Modify'),
                    Url::fromPath('reporting/report/edit', ['id' => $reportId]),
                    'edit',
                    [
                        'data-icinga-modal'   => true,
                        'data-no-icinga-ajax' => true
                    ]
                )
            );

            $actions->addHtml(
                new ActionLink(
                    $this->translate('Clone'),
                    Url::fromPath('reporting/report/clone', ['id' => $reportId]),
                    'clone',
                    [
                        'data-icinga-modal'   => true,
                        'data-no-icinga-ajax' => true
                    ]
                )
            );
        }

        if ($this->hasPermission('reporting/schedules')) {
            $actions->addHtml(
                new ActionLink(
                    $this->translate('Schedule'),
                    Url::fromPath('reporting/report/schedule', ['id' => $reportId]),
                    'calendar-empty',
                    [
                        'data-icinga-modal'   => true,
                        'data-no-icinga-ajax' => true
                    ]
                )
            );
        }

        $actions
            ->add($download)
            ->addHtml(
                new ActionLink(
                    $this->translate('Send'),
                    Url::fromPath('reporting/report/send', ['id' => $reportId]),
                    'forward',
                    [
                        'data-icinga-modal'   => true,
                        'data-no-icinga-ajax' => true
                    ]
                )
            );

        return $actions;
    }
}
