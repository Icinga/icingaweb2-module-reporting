<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Actions;

use Icinga\Application\Config;
use Icinga\Application\Logger;
use Icinga\Module\Pdfexport\ProvidedHook\Pdfexport;
use Icinga\Module\Reporting\Hook\ActionHook;
use Icinga\Module\Reporting\Mail;
use Icinga\Module\Reporting\Report;
use ipl\Html\Form;
use ipl\Stdlib\Str;
use ipl\Validator\CallbackValidator;
use ipl\Validator\EmailAddressValidator;
use Throwable;

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

        $mail->setFrom(
            Config::module('reporting', 'config', true)->get('mail', 'from', 'reporting@icinga')
        );

        if (isset($config['subject'])) {
            $mail->setSubject($config['subject']);
        }

        /** @var array<int, string> $recipients */
        $recipients = preg_split('/[\s,]+/', $config['recipients']);
        $recipients = array_filter($recipients);

        switch ($config['type']) {
            case 'pdf':
                /** @var Pdfexport $exporter */
                $exporter = Pdfexport::first();
                $exporter->asyncHtmlToPdf($report->toPdf())->then(
                    function ($pdf) use ($mail, $name, $recipients) {
                        $mail->attachPdf($pdf, $name);
                        $mail->send(null, $recipients);
                    }
                )->catch(function (Throwable $e) {
                    Logger::error($e);
                    Logger::debug($e->getTraceAsString());
                });

                return;
            case 'csv':
                $mail->attachCsv($report->toCsv(), $name);

                break;
            case 'json':
                $mail->attachJson($report->toJson(), $name);

                break;
            default:
                throw new \InvalidArgumentException();
        }

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
            'required' => true,
            'label'    => t('Type'),
            'options'  => $types
        ]);

        $form->addElement('text', 'subject', [
            'label'       => t('Subject'),
            'placeholder' => Mail::DEFAULT_SUBJECT
        ]);

        $form->addElement('textarea', 'recipients', [
            'required' => true,
            'label'    => t('Recipients'),
            'validators' => [
                new CallbackValidator(function ($value, CallbackValidator $validator): bool {
                    $mailValidator = new EmailAddressValidator();
                    $mails = Str::trimSplit($value);
                    foreach ($mails as $mail) {
                        if (! $mailValidator->isValid($mail)) {
                            $validator->addMessage(...$mailValidator->getMessages());

                            return false;
                        }
                    }

                    return true;
                })
            ]
        ]);
    }
}
