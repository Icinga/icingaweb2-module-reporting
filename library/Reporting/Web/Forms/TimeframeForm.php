<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use DateTime;
use Icinga\Module\Reporting\Database;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Web\Compat\CompatForm;

class TimeframeForm extends CompatForm
{
    use Database;

    protected $id;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->addElement('text', 'name', [
            'required'  => true,
            'label'     => $this->translate('Name')
        ]);

        $elementTypeStart = 'localDateTime';
        $populatedStartValue = $this->getPopulatedValue('start');
        if ($populatedStartValue && ! $this->isDatetimeFormat($populatedStartValue)) {
            $elementTypeStart = 'text';
        }

        $this->addElement('checkbox', 'start-checkbox', [
            'label'              => $this->translate('Start textual datetime'),
            'checkedValue'       => 'text',
            'uncheckedValue'     => 'localDateTime',
            'value'              => $elementTypeStart,
            'class'              => 'autosubmit'
        ]);

        $elementTypeStart = $this->getValue('start-checkbox');
        $this->addElement($elementTypeStart, 'start', [
            'required'      => true,
            'label'         => 'Start',
            'placeholder'   => $this->translate('Provide a textual datetime description')
        ]);

        $elementTypeEnd = 'localDateTime';
        $populatedEndValue = $this->getPopulatedValue('end');
        if ($populatedEndValue && ! $this->isDatetimeFormat($populatedEndValue)) {
            $elementTypeEnd = 'text';
        }

        $this->addElement('checkbox', 'end-checkbox', [
            'label'              => $this->translate('End textual datetime'),
            'checkedValue'       => 'text',
            'uncheckedValue'     => 'localDateTime',
            'value'              => $elementTypeEnd,
            'class'              => 'autosubmit'
        ]);

        $elementTypeEnd = $this->getValue('end-checkbox');
        $this->addElement($elementTypeEnd, 'end', [
            'required'      => true,
            'label'         => $this->translate('End'),
            'placeholder'   => $this->translate('Provide a textual datetime description'),
        ]);

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null
                ? $this->translate('Create Time Frame')
                : $this->translate('Update Time Frame')
        ]);

        if ($this->id !== null) {
            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'             => $this->translate('Remove Time Frame'),
                'class'             => 'btn-remove',
                'formnovalidate'    => true
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

        $startTimestamp = $values['start'];
        $endTimestamp = $values['end'];

        if ($values['start-checkbox'] === 'localDateTime') {
            $startTimestamp = $startTimestamp->format('Y-m-d H:i:s');
        }

        if ($values['end-checkbox'] === 'localDateTime') {
            $endTimestamp = $endTimestamp->format('Y-m-d H:i:s');
        }

        if ($this->id === null) {
            $db->insert('timeframe', [
                'name'  => $values['name'],
                'start' => $startTimestamp,
                $end    => $endTimestamp,
                'ctime' => $now,
                'mtime' => $now
            ]);
        } else {
            $db->update('timeframe', [
                'name'  => $values['name'],
                'start' => $startTimestamp,
                $end    => $endTimestamp,
                'mtime' => $now
            ], ['id = ?' => $this->id]);
        }
    }

    private function isDatetimeFormat($value)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', $value) !== false;
    }
}
