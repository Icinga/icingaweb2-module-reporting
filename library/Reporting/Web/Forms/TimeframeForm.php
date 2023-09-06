<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use DateTime;
use Exception;
use Icinga\Module\Reporting\Database;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\FormElement\LocalDateTimeElement;
use ipl\Html\HtmlDocument;
use ipl\Validator\CallbackValidator;
use ipl\Web\Compat\CompatForm;

class TimeframeForm extends CompatForm
{
    use Database;

    /** @var int */
    protected $id;

    /**
     * Create a new form instance with the given report
     *
     * @param int $id
     *
     * @return static
     */
    public static function fromId(int $id): self
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
        $this->addElement('text', 'name', [
            'required'    => true,
            'label'       => $this->translate('Name'),
            'description' => $this->translate('A unique name of this timeframe')
        ]);

        $default = new DateTime('00:00:00');
        $start = $this->getPopulatedValue('start', $default);
        if (! $start instanceof DateTime) {
            $datetime = DateTime::createFromFormat(LocalDateTimeElement::FORMAT, $start);
            if ($datetime) {
                $start = $datetime;
            }
        }

        $relativeStart = $this->getPopulatedValue('relative-start', $start instanceof DateTime ? 'n' : 'y');
        $this->addElement('checkbox', 'relative-start', [
            'required' => false,
            'class'    => 'autosubmit',
            'value'    => $relativeStart,
            'label'    => $this->translate('Relative Start')
        ]);

        if ($relativeStart === 'n') {
            if (! $start instanceof DateTime) {
                $start = $default;
                $this->clearPopulatedValue('start');
            }

            $this->addElement(
                new LocalDateTimeElement('start', [
                    'required'    => true,
                    'value'       => $start,
                    'label'       => $this->translate('Start'),
                    'description' => $this->translate('Specifies the start time of this timeframe')
                ])
            );
        } else {
            $this->addElement('text', 'start', [
                'required'    => true,
                'label'       => $this->translate('Start'),
                'placeholder' => $this->translate('First day of this month'),
                'description' => $this->translate('Specifies the start time of this timeframe'),
                'validators' => [
                    new CallbackValidator(function ($value, CallbackValidator $validator) {
                        if ($value !== null) {
                            try {
                                new DateTime($value);
                            } catch (Exception $_) {
                                $validator->addMessage($this->translate('Invalid textual date time'));

                                return false;
                            }
                        }

                        return true;
                    })
                ]
            ]);
        }

        $default = new DateTime('23:59:59');
        $end = $this->getPopulatedValue('end', $default);
        if (! $end instanceof DateTime) {
            $datetime = DateTime::createFromFormat(LocalDateTimeElement::FORMAT, $end);
            if ($datetime) {
                $end = $datetime;
            }
        }

        $relativeEnd = $this->getPopulatedValue('relative-end', $end instanceof DateTime ? 'n' : 'y');
        if ($relativeStart === 'y') {
            $this->addElement('checkbox', 'relative-end', [
                'required' => false,
                'class'    => 'autosubmit',
                'value'    => $relativeEnd,
                'label'    => $this->translate('Relative End')
            ]);
        }

        if ($relativeEnd === 'n' || $relativeStart === 'n') {
            if (! $end instanceof DateTime) {
                $end = $default;
                $this->clearPopulatedValue('end');
            }

            $this->addElement(
                new LocalDateTimeElement('end', [
                    'required'    => true,
                    'value'       => $end,
                    'label'       => $this->translate('End'),
                    'description' => $this->translate('Specifies the end time of this timeframe')
                ])
            );
        } else {
            $this->addElement('text', 'end', [
                'required'    => true,
                'label'       => $this->translate('End'),
                'placeholder' => $this->translate('Last day of this month'),
                'description' => $this->translate('Specifies the end time of this timeframe'),
                'validators' => [
                    new CallbackValidator(function ($value, CallbackValidator $validator) {
                        if ($value !== null) {
                            try {
                                new DateTime($value);
                            } catch (Exception $_) {
                                $validator->addMessage($this->translate('Invalid textual date time'));

                                return false;
                            }
                        }

                        return true;
                    })
                ]
            ]);
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null
                ? $this->translate('Create Time Frame')
                : $this->translate('Update Time Frame')
        ]);

        if ($this->id !== null) {
            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'          => $this->translate('Remove Time Frame'),
                'class'          => 'btn-remove',
                'formnovalidate' => true
            ]);
            $this->registerElement($removeButton);

            /** @var HtmlDocument $wrapper */
            $wrapper = $this->getElement('submit')->getWrapper();
            $wrapper->prepend($removeButton);
        }
    }

    public function onSuccess()
    {
        $db = $this->getDb();

        if ($this->getPopulatedValue('remove')) {
            $db->delete('timeframe', ['id = ?' => $this->id]);

            return;
        }

        $values = $this->getValues();
        if ($values['start'] instanceof DateTime) {
            $values['start'] = $values['start']->format(LocalDateTimeElement::FORMAT);
        }

        if ($values['end'] instanceof DateTime) {
            $values['end'] = $values['end']->format(LocalDateTimeElement::FORMAT);
        }

        $now = time() * 1000;

        $end = $db->quoteIdentifier('end');

        if ($this->id === null) {
            $db->insert('timeframe', [
                'name'  => $values['name'],
                'start' => $values['start'],
                $end    => $values['end'],
                'ctime' => $now,
                'mtime' => $now
            ]);
        } else {
            $db->update('timeframe', [
                'name'  => $values['name'],
                'start' => $values['start'],
                $end    => $values['end'],
                'mtime' => $now
            ], ['id = ?' => $this->id]);
        }
    }
}
