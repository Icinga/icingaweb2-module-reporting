<?php
// Icinga Reporting | (c) 2020 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\DivDecorator;
use Icinga\Module\Reporting\Web\Flatpickr;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;

class DowntimesForm extends Form
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
        $this->setDefaultElementDecorator(new DivDecorator());

        $this->addElement('text', 'object_id', [
            'required'      => true,
            'label'         => 'ObjectID'
        ]);

        $flatpickr = new Flatpickr();

        $this->addDecoratedElement($flatpickr, 'text', 'start_time', [
            'required'            => true,
            'label'               => 'Start',
            'placeholder'         => 'Select a start date or provide a textual datetime description',
            'data-allow-input'    => true,
            'data-enable-time'    => true,
            'data-enable-seconds' => true,
            'data-default-hour'   => '00'
        ]);

        $this->addDecoratedElement($flatpickr, 'text', 'end_time', [
            'required'             => true,
            'label'                => 'End',
            'placeholder'          => 'Select a end date or provide a textual datetime description',
            'data-allow-input'     => true,
            'data-enable-time'     => true,
            'data-enable-seconds'  => true,
            'data-default-hour'    => '23',
            'data-default-minute'  => '59',
            'data-default-seconds' => '59'
        ]);

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Downtime' : 'Update Downtime'
        ]);

        if ($this->id !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => 'Remove Downtime',
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);

            $resource = MonitoringBackend::instance()->getName();

            /** @var SubmitElementInterface $remove */
            $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb($resource)->delete('icinga_reporting_fake_downtime', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->valid = true;

                return;
            }
        }

    }

    public function onSuccess()
    {
        $db = MonitoringBackend::instance()->getName();

        $values = $this->getValues();

        if ($this->id === null) {
            $this->getDb($db)->insert('icinga_reporting_fake_downtime', [
                'object_id'     => $values['object_id'],
                'start_time'    => $values['start_time'],
                'end_time'      => $values['end_time']
            ]);
        } else {
            $this->getDb($db)->update('icinga_reporting_fake_downtime', [
                'object_id'     => $values['object_id'],
                'start_time'    => $values['start_time'],
                'end_time'      => $values['end_time']
            ], ['id = ?' => $this->id]);
        }
    }
}
