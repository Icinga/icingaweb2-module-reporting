<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting;

use Zend_Mail;
use Zend_Mail_Transport_Sendmail;
use Zend_Mime;
use Zend_Mime_Part;

class Mail
{
    /** @var string */
    const DEFAULT_SUBJECT = 'Icinga Reporting';

    /** @var string */
    protected $from;

    /** @var string */
    protected $subject = self::DEFAULT_SUBJECT;

    /** @var Zend_Mail_Transport_Sendmail */
    protected $transport;

    /** @var array */
    protected $attachments = [];

    /**
     * Get the from part
     *
     * @return  string
     */
    public function getFrom()
    {
        if (isset($this->from)) {
            return $this->from;
        }

        if (isset($_SERVER['SERVER_ADMIN'])) {
            $this->from = $_SERVER['SERVER_ADMIN'];

            return $this->from;
        }

        foreach (['HTTP_HOST', 'SERVER_NAME', 'HOSTNAME'] as $key) {
            if (isset($_SEVER[$key])) {
                $this->from = 'icinga-reporting@' . $_SERVER[$key];

                return $this->from;
            }
        }

        $this->from = 'icinga-reporting@localhost';

        return $this->from;
    }

    /**
     * Set the from part
     *
     * @param   string $from
     *
     * @return  $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get the subject
     *
     * @return  string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the subject
     *
     * @param   string $subject
     *
     * @return  $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the mail transport
     *
     * @return  Zend_Mail_Transport_Sendmail
     */
    public function getTransport()
    {
        if (! isset($this->transport)) {
            $this->transport = new Zend_Mail_Transport_Sendmail('-f ' . escapeshellarg($this->getFrom()));
        }

        return $this->transport;
    }

    public function attachCsv($csv, $filename)
    {
        if (is_array($csv)) {
            $csv = Str::putcsv($csv);
        }

        $attachment = new Zend_Mime_Part($csv);

        $attachment->type = 'text/csv';
        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = Zend_Mime::ENCODING_BASE64;
        $attachment->filename = basename($filename, '.csv') . '.csv';

        $this->attachments[] = $attachment;

        return $this;
    }

    public function attachJson($json, $filename)
    {
        if (is_array($json)) {
            $json = json_encode($json);
        }

        $attachment = new Zend_Mime_Part($json);

        $attachment->type = 'application/json';
        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = Zend_Mime::ENCODING_BASE64;
        $attachment->filename = basename($filename, '.json') . '.json';

        $this->attachments[] = $attachment;

        return $this;
    }

    public function attachPdf($pdf, $filename)
    {
        $attachment = new Zend_Mime_Part($pdf);

        $attachment->type = 'application/pdf';
        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = Zend_Mime::ENCODING_BASE64;
        $attachment->filename = basename($filename, '.pdf') . '.pdf';

        $this->attachments[] = $attachment;

        return $this;
    }

    public function send($body, $recipient)
    {
        $mail = new Zend_Mail('UTF-8');

        $mail->setFrom($this->getFrom(), '');
        $mail->addTo($recipient);
        $mail->setSubject($this->getSubject());

        if ($body && (strlen($body) !== strlen(strip_tags($body)))) {
            $mail->setBodyHtml($body);
        } else {
            $mail->setBodyText($body ?? '');
        }

        foreach ($this->attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->send($this->getTransport());
    }
}
