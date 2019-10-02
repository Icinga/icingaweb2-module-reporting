<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Actions;

use Icinga\Application\Config;
use Icinga\Application\Hook;
use Icinga\Module\Reporting\Hook\ActionHook;
use Icinga\Module\Reporting\Mail;
use Icinga\Module\Reporting\Report;
use Icinga\Util\StringHelper;
use Icinga\Web\StyleSheet;
use ipl\Html\Form;
use ipl\Html\Html;
use ipl\Html\HtmlString;

class SendMail extends ActionHook
{
    public function getName()
    {
        return 'Send Mail';
    }

    public function execute(Report $report, array $config)
    {
        $name = sprintf(
            '%s (%s) %s',
            $report->getName(),
            $report->getTimeframe()->getName(),
            date('Y-m-d H:i')
        );

        $mail = new Mail();

        $mail->setFrom(Config::module('reporting')->get('mail', 'from', 'reporting@icinga'));

        switch ($config['type']) {
            case 'pdf':
                $pdfexport = null;

                if (Hook::has('Pdfexport')) {
                    $pdfexport = Hook::first('Pdfexport');

                    if (! $pdfexport->isSupported()) {
                        throw new \Exception(
                            sprintf("Can't export: %s does not support exporting PDFs", get_class($pdfexport))
                        );
                    }
                }

                if (! $pdfexport) {
                    throw new \Exception("Can't export: No module found which provides PDF export");
                }

                $html = Html::tag(
                    'html',
                    null,
                    [
                        Html::tag(
                            'head',
                            null,
                            Html::tag(
                                'style',
                                null,
                                new HtmlString(StyleSheet::forPdf())
                            )
                        ),
                        Html::tag(
                            'body',
                            null,
                            Html::tag(
                                'div',
                                ['class' => 'icinga-module module-reporting'],
                                new HtmlString($report->toHtml())
                            )
                        )
                    ]
                );

                $mail->attachPdf($pdfexport->htmlToPdf((string) $html), $name);

                break;
            case 'csv':
                $mail->attachCsv($report->toCsv(), $name);

                break;
            case 'json':
                $mail->attachJson($report->toJson(), $name);

                break;
            default:
                throw new \InvalidArgumentException();

        }

        $recipients = StringHelper::trimSplit($config['recipients']);

        $mail->send(null, $recipients);
    }

    public function initConfigForm(Form $form, Report $report)
    {
        $types = ['pdf' => 'PDF'];

        if ($report->providesData()) {
            $types['csv'] = 'CSV';
            $types['json'] = 'JSON';
        }

        $form->addElement('select', 'type', [
            'required'  => true,
            'label'     => 'Type',
            'options'   => $types
        ]);

        $form->addElement('textarea', 'recipients', [
            'required' => true,
            'label'    => 'Recipients'
        ]);
    }
}
