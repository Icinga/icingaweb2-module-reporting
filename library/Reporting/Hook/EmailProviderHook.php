<?php

namespace Icinga\Module\Reporting\Hook;

use Icinga\Application\Hook;

abstract class EmailProviderHook
{
    /**
     * Get all Contact eMails
     *
     * @return mixed
     */
    abstract public function getContactEmails();

    /**
     * @return array
     */
    final public static function getProvider(): array
    {
        return Hook::all('Reporting/EmailProvider');
    }
}
