<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Actions;

use Icinga\Application\Config;
use Icinga\Module\Pdfexport\ProvidedHook\Pdfexport;
use Icinga\Module\Reporting\Hook\ActionHook;
use Icinga\Module\Reporting\Mail;
use Icinga\Module\Reporting\Report;
use ipl\Html\Form;
use ipl\Stdlib\Str;
use ipl\Validator\CallbackValidator;
use ipl\Validator\EmailAddressValidator;

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

        switch ($config['type']) {
            case 'pdf':
                $mail->attachPdf(Pdfexport::first()->htmlToPdf($report->toPdf()), $name);

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

        /** @var array<int, string> $recipients */
        $recipients = preg_split('/[\s,]+/', $config['recipients']);

        $mail->send(null, array_filter($recipients));
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
