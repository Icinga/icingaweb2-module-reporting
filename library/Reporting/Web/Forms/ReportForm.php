<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Form;
use ipl\Validator\CallbackValidator;
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
            'required'    => true,
            'label'       => $this->translate('Name'),
            'description' => $this->translate(
                'A unique name of this report. It is used when exporting to pdf, json or csv format'
                . ' and also when listing the reports in the cli'
            ),
            'validators' => [
                'Callback' => function ($value, CallbackValidator $validator) {
                    if ($value !== null && strpos($value, '..') !== false) {
                        $validator->addMessage(
                            $this->translate('Double dots are not allowed in the report name')
                        );

                        return false;
                    }

                    return true;
                }
            ]
        ]);

        $this->addElement('select', 'timeframe', [
            'required'    => true,
            'class'       => 'autosubmit',
            'label'       => $this->translate('Timeframe'),
            'options'     => [null => $this->translate('Please choose')] + $this->listTimeframes(),
            'description' => $this->translate(
                'Specifies the time frame in which this report is to be generated'
            )
        ]);

        $this->addElement('select', 'template', [
            'label'       => $this->translate('Template'),
            'options'     => [null => $this->translate('Please choose')] + $this->listTemplates(),
            'description' => $this->translate(
                'Specifies the template to use when exporting this report to pdf. (Default Icinga template)'
            )
        ]);

        $this->addElement('select', 'reportlet', [
            'required'    => true,
            'class'       => 'autosubmit',
            'label'       => $this->translate('Report'),
            'options'     => [null => $this->translate('Please choose')] + $this->listReports(),
            'description' => $this->translate('Specifies the type of the reportlet to be generated')
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
            'label' => $this->id === null ? $this->translate('Create Report') : $this->translate('Update Report')
        ]);

        if ($this->id !== null) {
            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'          => $this->translate('Remove Report'),
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
