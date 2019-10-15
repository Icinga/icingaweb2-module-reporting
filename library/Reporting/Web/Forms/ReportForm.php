<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Web\DivDecorator;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;

class ReportForm extends Form
{
    use Database;
    use ProvidedReports;

    /** @var bool Hack to disable the {@link onSuccess()} code upon deletion of the report */
    protected $callOnSuccess;

    protected $id;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new DivDecorator());

        $this->addElement('text', 'name', [
            'required'  => true,
            'label'     => 'Name'
        ]);

        $this->addElement('select', 'timeframe', [
            'required'  => true,
            'label'     => 'Timeframe',
            'options'   => [null => 'Please choose'] + $this->listTimeframes(),
            'class'     => 'autosubmit'
        ]);

        $this->addElement('select', 'template', [
            'label'     => 'Template',
            'options'   => [null => 'Please choose'] + $this->listTemplates()
        ]);

        $this->addElement('select', 'reportlet', [
            'required'  => true,
            'label'     => 'Report',
            'options'   => [null => 'Please choose'] + $this->listReports(),
            'class'     => 'autosubmit'
        ]);

        $values = $this->getValues();

        if (isset($values['reportlet'])) {
            $config = new Form();
//            $config->populate($this->getValues());

            /** @var \Icinga\Module\Reporting\Hook\ReportHook $reportlet */
            $reportlet = new $values['reportlet'];

            $reportlet->initConfigForm($config);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);
            }
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Report' : 'Update Report'
        ]);

        if ($this->id !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => 'Remove Report',
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);

            /** @var SubmitElementInterface $remove */
            $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb()->delete('report', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->callOnSuccess = false;
                $this->valid = true;

                return;
            }
        }

        // TODO(el): Remove once ipl/html's TextareaElement sets the value as content
        foreach ($this->getElements() as $element) {
            if ($element instanceof TextareaElement && $element->hasValue()) {
                $element->setContent($element->getValue());
            }
        }
    }

    public function onSuccess()
    {
        if ($this->callOnSuccess === false) {
            return;
        }

        $db = $this->getDb();

        $values = $this->getValues();

        $now = time() * 1000;

        $db->beginTransaction();

        if ($this->id === null) {
            $db->insert('report', [
                'name'         => $values['name'],
                'author'       => Auth::getInstance()->getUser()->getUsername(),
                'timeframe_id' => $values['timeframe'],
                'template_id'  => $values['template'],
                'ctime'        => $now,
                'mtime'        => $now
            ]);

            $reportId = $db->lastInsertId();
        } else {
            $db->update('report', [
                'name'         => $values['name'],
                'timeframe_id' => $values['timeframe'],
                'template_id'  => $values['template'],
                'mtime'        => $now
            ], ['id = ?' => $this->id]);

            $reportId = $this->id;
        }

        unset($values['name']);
        unset($values['timeframe']);

        if ($this->id !== null) {
            $db->delete('reportlet', ['report_id = ?' => $reportId]);
        }

        $db->insert('reportlet', [
            'report_id' => $reportId,
            'class'     => $values['reportlet'],
            'ctime'     => $now,
            'mtime'     => $now
        ]);

        $reportletId = $db->lastInsertId();

        unset($values['reportlet']);

        foreach ($values as $name => $value) {
            $db->insert('config', [
                'reportlet_id'  => $reportletId,
                'name'          => $name,
                'value'         => $value,
                'ctime'         => $now,
                'mtime'         => $now
            ]);
        }

        $db->commitTransaction();
    }
}
