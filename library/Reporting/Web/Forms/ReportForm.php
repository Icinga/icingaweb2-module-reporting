<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Hook\ReportHook;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Form;
use ipl\Html\FormElement\Collection;
use ipl\Html\FormElement\Fieldset;
use ipl\Web\Compat\CompatForm;
use ipl\Web\FormDecorator\IcingaFormDecorator;
use ipl\Web\Widget\Icon;

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

    public function __construct()
    {
        $this->on(static::ON_SENT, function () {
            if ($this->getPressedSubmitElement() && $this->getPressedSubmitElement()->getName() === 'remove') {
                $this->getDb()->delete('report', ['id = ?' => $this->id]);
            }
        });
    }

    public static function fromReport(Report $report): ReportForm
    {
        $self = new static();
        $self->setId($report->getId());

        return $self;
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
            )
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

        $collection = new Collection('reportlet');
        $collection->setLabel('Reportlets');


        $collection
            ->setAddElement('select', 'reportlet', [
                'required' => false,
                'label'    => 'Reportlet',
                'options'  => [null => 'Please choose'] + $this->listReports(),
                'class'    => 'autosubmit'
            ])
            ->setRemoveElement('submitButton', 'remove_reportlet', [
                'label'          => new Icon('trash'),
                'class'          => 'btn-remove-reportlet',
                'formnovalidate' => true,
                'title'          => 'Remove Reportlet'
            ]);

        $collection->onAssembleGroup(function (/** @var Fieldset $group */ $group, $addElement, $removeElement) {
            $group->setDefaultElementDecorator(new IcingaFormDecorator());

            $this->decorate($addElement);

            $group
                ->registerElement($addElement)
                ->addHtml($addElement);

            $group->registerElement($removeElement);
            $addElement->getWrapper()->ensureAssembled()->add($removeElement);

            $reportletClass = $group->getPopulatedValue('reportlet');
            if (! empty($reportletClass)) {
                $config = new Form();

                /** @var ReportHook $reportlet */
                $reportlet = new $reportletClass();
                $reportlet->initConfigForm($config);

                foreach ($config->getElements() as $element) {
                    $this->decorate($element);

                    $group
                        ->registerElement($element)
                        ->addHtml($element);
                }
            }
        });

        $this->registerElement($collection);
        $this->add($collection);

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? 'Create Report' : 'Update Report'
        ]);
        $this->setSubmitButton($this->getElement('submit'));

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

        if ($this->id !== null) {
            $db->delete('reportlet', ['report_id = ?' => $reportId]);
        }

        foreach ($values['reportlet'] as $reportlet) {
            array_walk($reportlet, function (&$value) {
                if ($value === '') {
                    $value = null;
                }
            });

            if (empty($reportlet['reportlet'])) {
                continue;
            }

            $db->insert('reportlet', [
                'report_id' => $reportId,
                'class'     => $reportlet['reportlet'],
                'ctime'     => $now,
                'mtime'     => $now
            ]);

            $reportletId = $db->lastInsertId();

            foreach ($reportlet as $key => $value) {
                if ($key === 'reportlet') {
                    continue;
                }

                foreach ($values as $name => $value) {
                    $db->insert('config', [
                        'reportlet_id' => $reportletId,
                        'name'         => $name,
                        'value'        => $value,
                        'ctime'        => $now,
                        'mtime'        => $now
                    ]);

                    $reportletId = $db->lastInsertId();

                    foreach ($reportlet as $key => $value) {
                        if ($key === '__class') {
                            continue;
                        }

                        $db->insert('config', [
                            'reportlet_id' => $reportletId,
                            'name'         => $key,
                            'value'        => $value,
                            'ctime'        => $now,
                            'mtime'        => $now
                        ]);
                    }
                }
            }
        }

        $db->commitTransaction();
    }
}
