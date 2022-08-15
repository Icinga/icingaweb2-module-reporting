<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Generator;
use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Hook\ReportHook;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\Forms\Decorator\CompatDecorator;
use ipl\Html\Attributes;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Form;
use ipl\Html\FormElement\BaseFormElement;
use ipl\Html\Html;
use ipl\Web\Compat\CompatForm;
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

        $this->addElement('select', 'reportlet', [
            'required'    => true,
            'class'       => 'autosubmit',
            'label'       => $this->translate('Report'),
            'options'     => [null => $this->translate('Please choose')] + $this->listReports(),
            'description' => $this->translate('Specifies the type of the reportlet to be generated')
        ]);
        $reportlets = $this->getPopulatedValue('reportlet') ?? [['__class' => '']];
        $hasEmptyReportlet = false;

        if (isset($values['reportlet'])) {
            $config = new Form();
//            $config->populate($this->getValues());

            /** @var \Icinga\Module\Reporting\Hook\ReportHook $reportlet */
            $reportlet = new $values['reportlet']();

            $reportlet->initConfigForm($config);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);

                foreach ($reportlets as $reportlet) {
                    if (empty($reportlet['__class'])) {
                        $hasEmptyReportlet = true;
                    }
                }

                if (! $hasEmptyReportlet) {
                    $reportlets[] = ['__class' => ''];
                }

                foreach ($reportlets as $key => $reportlet) {
                    if (isset($this->ignoredReportletId) && $key === $this->ignoredReportletId) {
                        continue;
                    }
                    if ($this->getPopulatedValue('remove_report_' . $key) !== null && sizeof($reportlets) > 1) {
                        continue;
                    }

                    $wrapper = Html::tag('div');
                    if ($key !== array_keys($reportlets)[count($reportlets) - 1]) {
                        $wrapper->addAttributes(Attributes::create(['class' => 'reportlet-container']));
                    }

                    /** @var FormSubmitElement $select */
                    $select = $this->createElement('select', "reportlet[$key][__class]", [
                        'required' => sizeof($reportlets) <= 1,
                        'label'    => 'Reportlet',
                        'options'  => [null => 'Please choose'] + $this->listReports(),
                        'class'    => 'autosubmit'
                    ]);
                    $this
                        ->registerElement($select)
                        ->decorate($select);
                    $wrapper->addHtml($select);

                    if ($key !== array_keys($reportlets)[count($reportlets) - 1]) {
                        $remove = $this->createElement('submitButton', 'remove_report_' . $key, [
                            'label'          => new Icon('trash'),
                            'class'          => 'btn-remove-reportlet',
                            'formnovalidate' => true,
                            'title'          => 'Remove Reportlet'
                        ]);
                        $this->registerElement($remove);

                        $select->getWrapper()->ensureAssembled()->add($remove);
                    }

                    $values = $this->getValues();
                    if (isset($values["reportlet[$key][__class]"])) {
                        $config = new Form();

                        /** @var ReportHook $reportlet */
                        $reportlet = new $values["reportlet[$key][__class]"];
                        $reportlet->initConfigForm($config);

                        foreach ($config->getElements() as $element) {
                            /** @var $element BaseFormElement */
                            $element
                                ->setName("reportlet[$key][" . $element->getName() . "]")
                                ->setAttribute('class', 'tte');

                            $this
                                ->registerElement($element)
                                ->decorate($element);


                            $wrapper->addHtml($element);
                        }
                    }

                    $this->addHtml($wrapper);
                }

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

        foreach (array_filter($this->getPopulatedValue('reportlet')) as $reportlet) {
            array_walk($reportlet, function (&$value) {
                if ($value === '') {
                    $value = null;
                }
            });

            if (empty($reportlet['__class'])) {
                continue;
            }

            unset($values['reportlet']);

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

        $db->commitTransaction();
    }

    public function populate($values)
    {
        $flatten = [];

        foreach ($values as $name => $value) {
            if (is_array($value)) {
                foreach ($this->flattenArray($name, $value) as $flattenKey => $flattenValue) {
                    $flatten[$flattenKey] = $flattenValue;
                }
            }

            $flatten[$name] = $value;
        }

        parent::populate($flatten);

        return $this;
    }

    /**
     * Returns a generator containing the $values represented as strings
     *
     * @param       $key
     * @param array $values
     *
     * @return Generator
     */

    protected function flattenArray($key, array $values): Generator
    {
        foreach ($values as $_key => $_value) {
            $effectiveKey = sprintf('%s[%s]', $key, $_key);
            if (is_array($_value)) {
                yield from $this->flattenArray($effectiveKey, $_value);
            } else {
                yield sprintf("%s", $effectiveKey) => $_value;
            }
        }
    }
}
