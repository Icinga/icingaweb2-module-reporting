<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use DateTime;
use Icinga\Application\Version;
use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Hook\ActionHook;
use Icinga\Module\Reporting\ProvidedActions;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Flatpickr;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use Icinga\Web\Notification;
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

    /** @var int */
    protected $id;

    /**
     * Create a new form instance with the given report
     *
     * @param Report $report
     *
     * @return static
     */
    public static function fromReport(Report $report): self
    {
        $form = new static();
        $form->report = $report;

        $schedule = $report->getSchedule();

        if ($schedule !== null) {
            $form->setId($schedule->getId());

            $config = $schedule->getConfig();
            $config['action'] = $schedule->getAction();

            $form->populate($config);
        }

        return $form;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id): ScheduleForm
    {
        $this->id = $id;

        return $this;
    }

    public function hasBeenSubmitted(): bool
    {
        return $this->hasBeenSent() && (
                $this->getPopulatedValue('submit')
                || $this->getPopulatedValue('remove')
                || $this->getPopulatedValue('send')
            );
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
                'required'    => true,
                'label'       => t('Start'),
                'placeholder' => t('Choose date and time')
            ]);
        } else {
            $this->addDecoratedElement((new Flatpickr())->setAllowInput(false), 'text', 'start', [
                'required'    => true,
                'label'       => t('Start'),
                'placeholder' => t('Choose date and time')
            ]);
        }

        $this->addElement('select', 'frequency', [
            'required' => true,
            'label'    => $this->translate('Frequency'),
            'options'  => [null => $this->translate('Please choose')] + $frequency,
        ]);

        $this->addElement('select', 'action', [
            'required' => true,
            'label'    => $this->translate('Action'),
            'options'  => [null => $this->translate('Please choose')] + $this->listActions(),
            'class'    => 'autosubmit'
        ]);

        $values = $this->getValues();

        if (isset($values['action'])) {
            $config = new Form();
//            $config->populate($this->getValues());

            /** @var ActionHook $action */
            $action = new $values['action']();

            $action->initConfigForm($config, $this->report);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);
            }
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? $this->translate('Create Schedule') : $this->translate('Update Schedule')
        ]);

        if ($this->id !== null) {
            $sendButton = $this->createElement('submit', 'send', [
                'label'          => $this->translate('Send Report Now'),
                'formnovalidate' => true
            ]);
            $this->registerElement($sendButton);
            $this->getElement('submit')->getWrapper()->prepend($sendButton);

            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'          => $this->translate('Remove Schedule'),
                'class'          => 'btn-remove',
                'formnovalidate' => true
            ]);
            $this->registerElement($removeButton);
            $this->getElement('submit')->getWrapper()->prepend($removeButton);
        }
    }

    public function onSuccess()
    {
        $db = $this->getDb();

        if ($this->getPopulatedValue('remove')) {
            $db->delete('schedule', ['id = ?' => $this->id]);

            return;
        }

        $values = $this->getValues();
        if ($this->getPopulatedValue('send')) {
            $action = new $values['action']();
            $action->execute($this->report, $values);

            Notification::success($this->translate('Report sent successfully'));

            return;
        }

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
