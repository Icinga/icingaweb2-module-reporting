<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Flatpickr;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Web\Compat\CompatForm;

class TimeframeForm extends CompatForm
{
    use Database;
    use DecoratedElement;

    protected $id;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new CompatDecorator());

        $this->addElement('text', 'name', [
            'required'  => true,
            'label'     => 'Name'
        ]);

        $flatpickr = new Flatpickr();

        $this->addDecoratedElement($flatpickr, 'text', 'start', [
            'required'                      => true,
            'label'                         => 'Start',
            'placeholder'                   => 'Select a start date or provide a textual datetime description',
            'data-flatpickr-default-hour'   => '00'
        ]);

        $this->addDecoratedElement($flatpickr, 'text', 'end', [
            'required'                      => true,
            'label'                         => 'End',
            'placeholder'                   => 'Select a end date or provide a textual datetime description',
            'data-flatpickrDefaultHour'     => '23',
            'data-flatpickrDefaultMinute'   => '59',
            'data-flatpickrDefaultSeconds'  => '59'
        ]);

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Time Frame' : 'Update Time Frame'
        ]);

        if ($this->id !== null) {
            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'          => 'Remove Time Frame',
                'class'          => 'btn-remove',
                'formnovalidate' => true
            ]);
            $this->registerElement($removeButton);
            $this->getElement('submit')->getWrapper()->prepend($removeButton);

            if ($removeButton->hasBeenPressed()) {
                $this->getDb()->delete('timeframe', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->valid = true;

                return;
            }
        }
    }

    public function onSuccess()
    {
        $db = $this->getDb();

        $values = $this->getValues();

        $now = time() * 1000;

        $end = $db->quoteIdentifier('end');

        if ($this->id === null) {
            $db->insert('timeframe', [
                'name'  => $values['name'],
                'start' => $values['start'],
                $end    => $values['end'],
                'ctime' => $now,
                'mtime' => $now
            ]);
        } else {
            $db->update('timeframe', [
                'name'  => $values['name'],
                'start' => $values['start'],
                $end    => $values['end'],
                'mtime' => $now
            ], ['id = ?' => $this->id]);
        }
    }
}
