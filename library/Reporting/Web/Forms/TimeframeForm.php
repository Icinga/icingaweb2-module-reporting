<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use DateTime;
use Exception;
use Icinga\Module\Reporting\Database;
use ipl\Html\FormElement\LocalDateTimeElement;
use ipl\Html\HtmlDocument;
use ipl\Validator\CallbackValidator;
use ipl\Web\Compat\CompatForm;

class TimeframeForm extends CompatForm
{
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

        $start = $this->getPopulatedValue('start', new DateTime('00:00:00'));
        $canBeConverted = $start instanceof DateTime
            || DateTime::createFromFormat(LocalDateTimeElement::FORMAT, $start) !== false;
        $relativeStart = $this->getPopulatedValue('relative-start', $canBeConverted ? 'n' : 'y');
        $this->addElement('checkbox', 'relative-start', [
            'required' => false,
            'class'    => 'autosubmit',
            'value'    => $relativeStart,
            'label'    => $this->translate('Relative Start')
        ]);

        if ($relativeStart === 'n') {
            if (! $start instanceof DateTime) {
                $start = (new DateTime($start))->format(LocalDateTimeElement::FORMAT);
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

        $end = $this->getPopulatedValue('end', new DateTime('23:59:59'));
        $canBeConverted = $end instanceof DateTime
            || DateTime::createFromFormat(LocalDateTimeElement::FORMAT, $end) !== false;
        $relativeEnd = $this->getPopulatedValue('relative-end', $canBeConverted ? 'n' : 'y');
        if ($relativeStart === 'y') {
            $this->addElement('checkbox', 'relative-end', [
                'required' => false,
                'class'    => 'autosubmit',
                'value'    => $relativeEnd,
                'label'    => $this->translate('Relative End')
            ]);
        }

        $endDateValidator = new CallbackValidator(function ($value, CallbackValidator $validator) {
            if (! $value instanceof DateTime) {
                try {
                    $value = new DateTime($value);
                } catch (Exception $_) {
                    $validator->addMessage($this->translate('Invalid textual date time'));

                    return false;
                }
            }

            $start = $this->getValue('start');
            if (! $start instanceof DateTime) {
                $start = new DateTime($start);
            }

            if ($value <= $start) {
                $validator->addMessage($this->translate('End time must be greater than start time'));

                return false;
            }

            return true;
        });

        if ($relativeEnd === 'n' || $relativeStart === 'n') {
            if (! $end instanceof DateTime) {
                $end = (new DateTime($end))->format(LocalDateTimeElement::FORMAT);
                $this->clearPopulatedValue('end');
            }

            $this->addElement(
                new LocalDateTimeElement('end', [
                    'required'    => true,
                    'value'       => $end,
                    'label'       => $this->translate('End'),
                    'description' => $this->translate('Specifies the end time of this timeframe'),
                    'validators'  => [$endDateValidator]
                ])
            );
        } else {
            $this->addElement('text', 'end', [
                'required'    => true,
                'label'       => $this->translate('End'),
                'placeholder' => $this->translate('Last day of this month'),
                'description' => $this->translate('Specifies the end time of this timeframe'),
                'validators' => [$endDateValidator]
            ]);
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null
                ? $this->translate('Create Time Frame')
                : $this->translate('Update Time Frame')
        ]);

        if ($this->id !== null) {
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
        $db = Database::get();

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
