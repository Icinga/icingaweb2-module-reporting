<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use DateTime;
use Icinga\Application\Version;
use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedActions;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Flatpickr;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Form;
use ipl\Web\Compat\CompatForm;

class ScheduleForm extends CompatForm
{
    use Database;
    use DecoratedElement;
    use ProvidedActions;

    /** @var Report */
    protected $report;

    protected $id;

    public function setReport(Report $report)
    {
        $this->report = $report;

        $schedule = $report->getSchedule();

        if ($schedule !== null) {
            $this->setId($schedule->getId());

            $values = [
                'start'     => $schedule->getStart()->format('Y-m-d\\TH:i:s'),
                'frequency' => $schedule->getFrequency(),
                'action'    => $schedule->getAction()
            ] + $schedule->getConfig();

            $this->populate($values);
        }

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new CompatDecorator());

        $frequency = [
            'minutely' => 'Minutely',
            'hourly'   => 'Hourly',
            'daily'    => 'Daily',
            'weekly'   => 'Weekly',
            'monthly'  => 'Monthly'
        ];

        if (version_compare(Version::VERSION, '2.9.0', '>=')) {
            $this->addElement('localDateTime', 'start', [
                'required'      => true,
                'label'         => t('Start'),
                'placeholder'   => t('Choose date and time')
            ]);
        } else {
            $this->addDecoratedElement((new Flatpickr())->setAllowInput(false), 'text', 'start', [
                'required'         => true,
                'label'            => t('Start'),
                'placeholder'      => t('Choose date and time')
            ]);
        }

        $this->addElement('select', 'frequency', [
            'required'  => true,
            'label'     => 'Frequency',
            'options'   => [null => 'Please choose'] + $frequency,
        ]);

        $this->addElement('select', 'action', [
            'required'  => true,
            'label'     => 'Action',
            'options'   => [null => 'Please choose'] + $this->listActions(),
            'class'     => 'autosubmit'
        ]);

        $values = $this->getValues();

        if (isset($values['action'])) {
            $config = new Form();
//            $config->populate($this->getValues());

            /** @var \Icinga\Module\Reporting\Hook\ActionHook $action */
            $action = new $values['action'];

            $action->initConfigForm($config, $this->report);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);
            }
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Schedule' : 'Update Schedule'
        ]);

        if ($this->id !== null) {
            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'          => 'Remove Schedule',
                'class'          => 'btn-remove',
                'formnovalidate' => true
            ]);
            $this->registerElement($removeButton);
            $this->getElement('submit')->getWrapper()->prepend($removeButton);

            if ($removeButton->hasBeenPressed()) {
                $this->getDb()->delete('schedule', ['id = ?' => $this->id]);

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

        if (! $values['start'] instanceof DateTime) {
            $values['start'] = DateTime::createFromFormat('Y-m-d H:i:s', $values['start']);
        }

        $data = [
            'start'     => $values['start']->getTimestamp() * 1000,
            'frequency' => $values['frequency'],
            'action'    => $values['action'],
            'mtime'     => $now
        ];

        unset($values['start']);
        unset($values['frequency']);
        unset($values['action']);

        $data['config'] = json_encode($values);

        $db->beginTransaction();

        if ($this->id === null) {
            $db->insert('schedule', $data + [
                'author'    => Auth::getInstance()->getUser()->getUsername(),
                'report_id' => $this->report->getId(),
                'ctime'     => $now
            ]);
        } else {
            $db->update('schedule', $data, ['id = ?' => $this->id]);
        }

        $db->commitTransaction();
    }
}
