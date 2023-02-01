<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Form;
use ipl\Web\Compat\CompatForm;

class ReportForm extends CompatForm
{
    use Database;
    use ProvidedReports;

    protected $id;

    /**
     * Create a new form instance with the given report id
     *
     * @param $id
     *
     * @return static
     */
    public static function fromId($id): self
    {
        $form = new static();
        $form->id = $id;

        return $form;
    }

    public function hasBeenSubmitted(): bool
    {
        return $this->hasBeenSent() && ($this->getPopulatedValue('submit') || $this->getPopulatedValue('remove'));
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new CompatDecorator());

        $this->addElement('text', 'name', [
            'required' => true,
            'label'    => 'Name'
        ]);

        $this->addElement('select', 'timeframe', [
            'required' => true,
            'label'    => 'Timeframe',
            'options'  => [null => 'Please choose'] + $this->listTimeframes(),
            'class'    => 'autosubmit'
        ]);

        $this->addElement('select', 'template', [
            'label'   => 'Template',
            'options' => [null => 'Please choose'] + $this->listTemplates()
        ]);

        $this->addElement('select', 'reportlet', [
            'required' => true,
            'label'    => 'Report',
            'options'  => [null => 'Please choose'] + $this->listReports(),
            'class'    => 'autosubmit'
        ]);

        $values = $this->getValues();

        if (isset($values['reportlet'])) {
            $config = new Form();
//            $config->populate($this->getValues());

            /** @var \Icinga\Module\Reporting\Hook\ReportHook $reportlet */
            $reportlet = new $values['reportlet']();

            $reportlet->initConfigForm($config);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);
            }
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Report' : 'Update Report'
        ]);

        if ($this->id !== null) {
            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'          => 'Remove Report',
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
            $db->delete('report', ['id = ?' => $this->id]);

            return;
        }

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
                'reportlet_id' => $reportletId,
                'name'         => $name,
                'value'        => $value,
                'ctime'        => $now,
                'mtime'        => $now
            ]);
        }

        $db->commitTransaction();
    }
}
