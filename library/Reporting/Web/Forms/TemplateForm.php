<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Exception;
use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use ipl\Html\Contract\FormSubmitElement;
use ipl\Html\Html;
use ipl\Web\Compat\CompatForm;
use reportingipl\Html\FormElement\FileElement;

class TemplateForm extends CompatForm
{
    use Database;

    protected $template;

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Create a new form instance with the given report
     *
     * @param $template
     *
     * @return static
     */
    public static function fromTemplate($template): self
    {
        $form = new static();

        $form->template = $template;

        if ($template->settings) {
            $form->populate(array_filter($template->settings, function ($value) {
                // Don't populate files
                return ! is_array($value);
            }));
        }

        return $form;
    }

    public function hasBeenSubmitted(): bool
    {
        return $this->hasBeenSent() && ($this->getPopulatedValue('submit') || $this->getPopulatedValue('remove'));
    }

    protected function assemble()
    {
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(Html::tag('h2', 'Template Settings'));

        $this->addElement('text', 'name', [
            'label'       => $this->translate('Name'),
            'placeholder' => $this->translate('Template name'),
            'required'    => true
        ]);

        $this->add(Html::tag('h2', $this->translate('Cover Page Settings')));

        $this->addElement(new FileElement('cover_page_background_image', [
            'label'  => $this->translate('Background Image'),
            'accept' => 'image/png, image/jpeg'
        ]));

        if (
            $this->template !== null
            && isset($this->template->settings['cover_page_background_image'])
        ) {
            $this->add(Html::tag(
                'p',
                ['style' => ['margin-left: 14em;']],
                $this->translate('Upload a new background image to override the existing one')
            ));

            $this->addElement('checkbox', 'remove_cover_page_background_image', [
                'label' => $this->translate('Remove background image')
            ]);
        }

        $this->addElement(new FileElement('cover_page_logo', [
            'label'  => $this->translate('Logo'),
            'accept' => 'image/png, image/jpeg'
        ]));

        if (
            $this->template !== null
            && isset($this->template->settings['cover_page_logo'])
        ) {
            $this->add(Html::tag(
                'p',
                ['style' => ['margin-left: 14em;']],
                $this->translate('Upload a new logo to override the existing one')
            ));

            $this->addElement('checkbox', 'remove_cover_page_logo', [
                'label' => $this->translate('Remove Logo')
            ]);
        }

        $this->addElement('textarea', 'title', [
            'label'       => $this->translate('Title'),
            'placeholder' => $this->translate('Report title')
        ]);

        $this->addElement('text', 'color', [
            'label'       => $this->translate('Color'),
            'placeholder' => $this->translate('CSS color code')
        ]);

        $this->add(Html::tag('h2', $this->translate('Header Settings')));

        $this->addColumnSettings('header_column1', $this->translate('Column 1'));
        $this->addColumnSettings('header_column2', $this->translate('Column 2'));
        $this->addColumnSettings('header_column3', $this->translate('Column 3'));

        $this->add(Html::tag('h2', $this->translate('Footer Settings')));

        $this->addColumnSettings('footer_column1', $this->translate('Column 1'));
        $this->addColumnSettings('footer_column2', $this->translate('Column 2'));
        $this->addColumnSettings('footer_column3', $this->translate('Column 3'));

        $this->addElement('submit', 'submit', [
            'label' => $this->template === null
                ? $this->translate('Create Template')
                : $this->translate('Update Template')
        ]);

        if ($this->template !== null) {
            /** @var FormSubmitElement $removeButton */
            $removeButton = $this->createElement('submit', 'remove', [
                'label'          => $this->translate('Remove Template'),
                'class'          => 'btn-remove',
                'formnovalidate' => true
            ]);
            $this->registerElement($removeButton);
            $this->getElement('submit')->getWrapper()->prepend($removeButton);
        }
    }

    public function onSuccess()
    {
        if ($this->getPopulatedValue('remove')) {
            $this->getDb()->delete('template', ['id = ?' => $this->template->id]);

            return;
        }

        ini_set('upload_max_filesize', '10M');

        $settings = $this->getValues();

        try {
            /** @var $uploadedFile \GuzzleHttp\Psr7\UploadedFile */
            foreach ($this->getRequest()->getUploadedFiles() as $name => $uploadedFile) {
                if ($uploadedFile->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $settings[$name] = [
                    'mime_type' => $uploadedFile->getClientMediaType(),
                    'size'      => $uploadedFile->getSize(),
                    'content'   => base64_encode((string) $uploadedFile->getStream())
                ];
            }

            $db = $this->getDb();

            $now = time() * 1000;

            if ($this->template === null) {
                $db->insert('template', [
                    'name'     => $settings['name'],
                    'author'   => Auth::getInstance()->getUser()->getUsername(),
                    'settings' => json_encode($settings),
                    'ctime'    => $now,
                    'mtime'    => $now
                ]);
            } else {
                if ($this->getValue('remove_cover_page_background_image', 'n') === 'y') {
                    unset($settings['cover_page_background_image']);
                    unset($settings['remove_cover_page_background_image']);
                } elseif (
                    ! isset($settings['cover_page_background_image'])
                    && isset($this->template->settings['cover_page_background_image'])
                ) {
                    $settings['cover_page_background_image'] = $this->template->settings['cover_page_background_image'];
                }

                if ($this->getValue('remove_cover_page_logo', 'n') === 'y') {
                    unset($settings['cover_page_logo']);
                    unset($settings['remove_cover_page_logo']);
                } elseif (
                    ! isset($settings['cover_page_logo'])
                    && isset($this->template->settings['cover_page_logo'])
                ) {
                    $settings['cover_page_logo'] = $this->template->settings['cover_page_logo'];
                }

                foreach (['header', 'footer'] as $headerOrFooter) {
                    for ($i = 1; $i <= 3; ++$i) {
                        $type = "{$headerOrFooter}_column{$i}_type";

                        if ($settings[$type] === 'image') {
                            $value = "{$headerOrFooter}_column{$i}_value";

                            if (
                                ! isset($settings[$value])
                                && isset($this->template->settings[$value])
                            ) {
                                $settings[$value] = $this->template->settings[$value];
                            }
                        }
                    }
                }

                $db->update('template', [
                    'name'     => $settings['name'],
                    'settings' => json_encode($settings),
                    'mtime'    => $now
                ], ['id = ?' => $this->template->id]);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    protected function addColumnSettings($name, $label)
    {
        $type = "{$name}_type";
        $value = "{$name}_value";

        $this->addElement('select', $type, [
            'class'   => 'autosubmit',
            'label'   => $label,
            'options' => [
                null       => 'None',
                'text'     => 'Text',
                'image'    => 'Image',
                'variable' => 'Variable'
            ]
        ]);

        switch ($this->getValue($type, 'none')) {
            case 'image':
                $this->addElement(new FileElement($value, [
                    'label'  => 'Image',
                    'accept' => 'image/png, image/jpeg'
                ]));

                if (
                    $this->template !== null
                    && $this->template->settings[$type] === 'image'
                    && isset($this->template->settings[$value])
                ) {
                    $this->add(Html::tag(
                        'p',
                        ['style' => ['margin-left: 14em;']],
                        'Upload a new image to override the existing one'
                    ));
                }
                break;
            case 'variable':
                $this->addElement('select', $value, [
                    'label'   => 'Variable',
                    'options' => [
                        'report_title'          => 'Report Title',
                        'time_frame'            => 'Time Frame',
                        'time_frame_absolute'   => 'Time Frame (absolute)',
                        'page_number'           => 'Page Number',
                        'total_number_of_pages' => 'Total Number of Pages',
                        'page_of'               => 'Page Number + Total Number of Pages',
                        'date'                  => 'Date'
                    ],
                    'value'   => 'report_title'
                ]);
                break;
            case 'text':
                $this->addElement('text', $value, [
                    'label'       => 'Text',
                    'placeholder' => 'Column text'
                ]);
                break;
        }
    }
}
