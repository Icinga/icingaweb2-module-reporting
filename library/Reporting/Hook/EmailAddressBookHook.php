<?php

namespace Icinga\Module\Reporting\Hook;

use Icinga\Application\Hook;
use ipl\I18n\Translation;

abstract class EmailAddressBookHook
{
    use Translation;

    /**
     * Get a list of email addresses as email-label pairs
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->translate('E-Mail Address Books');
    }
}
