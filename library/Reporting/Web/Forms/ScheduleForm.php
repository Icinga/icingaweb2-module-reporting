<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Application\Icinga;
use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Hook\ActionHook;
use Icinga\Module\Reporting\ProvidedActions;
use Icinga\Module\Reporting\Report;
use Icinga\Util\Json;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Form;
use ipl\Html\HtmlElement;
use ipl\Scheduler\Contract\Frequency;
use ipl\Scheduler\Cron;
use ipl\Web\Compat\CompatForm;
use ipl\Web\FormElement\ScheduleElement;

use function ipl\Stdlib\get_php_type;

class ScheduleForm extends CompatForm
{
    use Database;
    use ProvidedActions;

    /** @var Report */
    protected $report;

    public function __construct()
    {
        $this->scheduleElement = new ScheduleElement('schedule_element');
        $this->scheduleElement->setIdProtector([Icinga::app()->getRequest(), 'protectId']);
    }

    public function getPartUpdates(): array
    {
        return $this->scheduleElement->prepareMultipartUpdate($this->getRequest());
    }

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
            $config = $schedule->getConfig();
            $config['action'] = $schedule->getAction();

            if (! isset($config['frequencyType'])) {
                $frequency = new Cron($config['frequency']);
                $frequency->startAt($config['start']);
            } else {
                /** @var Frequency $type */
                $type = $config['frequencyType'];
                $frequency = $type::fromJson($config['frequency']);
            }

            $config['schedule_element'] = $frequency;

            $form->populate($config);
        }

        return $form;
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
        $this->addElement('select', 'action', [
            'required'    => true,
            'class'       => 'autosubmit',
            'options'     => array_merge([null => $this->translate('Please choose')], $this->listActions()),
            'label'       => $this->translate('Action'),
            'description' => $this->translate('Specifies an action to be triggered by the scheduler')
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

        $this->addHtml(HtmlElement::create('div', ['class' => 'schedule-element-separator']));
        $this->addElement($this->scheduleElement);

        $schedule = $this->report->getSchedule();
        $this->addElement('submit', 'submit', [
            'label' => $schedule === null ? $this->translate('Create Schedule') : $this->translate('Update Schedule')
        ]);

        if ($schedule !== null) {
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
        $schedule = $this->report->getSchedule();
        if ($this->getPopulatedValue('remove')) {
            $db->delete('schedule', ['id = ?' => $schedule->getId()]);

            return;
        }

        $values = $this->getValues();
        if ($this->getPopulatedValue('send')) {
            $action = new $values['action']();
            $action->execute($this->report, $values);

            return;
        }

        $now = time() * 1000;
        if (isset($values['start'])) {
            unset($values['start']);
        }

        $action = $values['action'];
        unset($values['action']);
        unset($values['schedule_element']);

        $frequency = $this->scheduleElement->getValue();
        $values['frequency'] = json_encode($frequency);
        $values['frequencyType'] = get_php_type($frequency);
        $config = Json::encode($values);

        $db->beginTransaction();

        if ($schedule === null) {
            $db->insert('schedule', [
                'author'    => Auth::getInstance()->getUser()->getUsername(),
                'report_id' => $this->report->getId(),
                'ctime'     => $now,
                'mtime'     => $now,
                'action'    => $action,
                'config'    => $config
            ]);
        } else {
            $db->update('schedule', [
                'action' => $action,
                'config' => $config
            ], ['id = ?' => $schedule->getId()]);
        }

        $db->commitTransaction();
    }
}
