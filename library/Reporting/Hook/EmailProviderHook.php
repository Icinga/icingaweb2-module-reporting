<?php

namespace Icinga\Module\Reporting\Hook;

use Icinga\Application\Hook;

abstract class EmailProviderHook
{
    /**
     * Get all Contact eMails
     *
     * @return array
     */
    abstract public function getContactEmails(): array;

    /**
     * @return array
     */
    final public static function getProviders(): array
    {
        return Hook::all('Reporting/EmailProvider');
    }
}
