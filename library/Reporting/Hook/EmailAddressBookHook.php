<?php

namespace Icinga\Module\Reporting\Hook;

use Icinga\Application\Hook;

abstract class EmailAddressBookHook
{
    /**
     * Get all Contact eMails
     *
     * @return array
     */
    abstract public function listEmailAddresses(): array;

    /**
     * @return array
     */
    final public static function getEmailAddressBooks(): array
    {
        return Hook::all('Reporting/EmailAddressBook');
    }
}
