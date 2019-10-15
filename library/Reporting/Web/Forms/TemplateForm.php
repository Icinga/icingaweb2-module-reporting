<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\DivDecorator;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;
use ipl\Html\Html;
use reportingipl\Html\FormElement\FileElement;

class TemplateForm extends Form
{
    use Database;


    /** @var bool Hack to disable the {@link onSuccess()} code upon deletion of the template */
    protected $callOnSuccess;

    protected $template;

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        if ($template->settings) {
            $this->populate(array_filter($template->settings, function ($value) {
                // Don't populate files
                return ! is_array($value);
            }));
        }

        return $this;
    }

    protected function assemble()
    {
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->setDefaultElementDecorator(new DivDecorator());

        $this->add(Html::tag('h2', 'Template Settings'));

        $this->addElement('text', 'name', [
            'label'       => 'Name',
            'placeholder' => 'Template name',
            'required'    => true
        ]);

        $this->add(Html::tag('h2', 'Cover Page Settings'));

        $this->addElement(new FileElement('cover_page_background_image', [
            'label'  => 'Background Image',
            'accept' => 'image/png, image/jpeg'
        ]));

        if ($this->template !== null
                && isset($this->template->settings['cover_page_background_image'])
        ) {
            $this->add(Html::tag(
                'p',
                ['style' => ['margin-left: 15em;']],
                'Upload a new background image to override the existing one'
            ));

            $this->addElement('checkbox', 'remove_cover_page_background_image', [
                'label' => 'Remove background image'
            ]);
        }

        $this->addElement(new FileElement('cover_page_logo', [
            'label'  => 'Logo',
            'accept' => 'image/png, image/jpeg'
        ]));

        if ($this->template !== null
            && isset($this->template->settings['cover_page_logo'])
        ) {
            $this->add(Html::tag(
                'p',
                ['style' => ['margin-left: 15em;']],
                'Upload a new logo to override the existing one'
            ));

            $this->addElement('checkbox', 'remove_cover_page_logo', [
                'label' => 'Remove Logo'
            ]);
        }

        $this->addElement('textarea', 'title', [
            'label'       => 'Title',
            'placeholder' => 'Report title'
        ]);

        $this->addElement('text', 'color', [
            'label'       => 'Color',
            'placeholder' => 'CSS color code'
        ]);

        $this->add(Html::tag('h2', 'Header Settings'));

        $this->addColumnSettings('header_column1', 'Column 1');
        $this->addColumnSettings('header_column2', 'Column 2');
        $this->addColumnSettings('header_column3', 'Column 3');

        $this->add(Html::tag('h2', 'Footer Settings'));

        $this->addColumnSettings('footer_column1', 'Column 1');
        $this->addColumnSettings('footer_column2', 'Column 2');
        $this->addColumnSettings('footer_column3', 'Column 3');

        $this->addElement('submit', 'submit', [
            'label' => $this->template === null ? 'Create Template' : 'Update Template'
        ]);

        if ($this->template !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => 'Remove Template',
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);

            /** @var SubmitElementInterface $remove */
            $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb()->delete('template', ['id = ?' => $this->template->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->callOnSuccess = false;
                $this->valid = true;

                return;
            }
        }
    }

    public function onSuccess()
    {
        if ($this->callOnSuccess === false) {
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
                if (isset($settings['remove_cover_page_background_image'])) {
                    unset($settings['cover_page_background_image']);
                    unset($settings['remove_cover_page_background_image']);
                } elseif (! isset($settings['cover_page_background_image'])
                    && isset($this->template->settings['cover_page_background_image'])
                ) {
                    $settings['cover_page_background_image'] = $this->template->settings['cover_page_background_image'];
                }

                if (isset($settings['remove_cover_page_logo'])) {
                    unset($settings['cover_page_logo']);
                    unset($settings['remove_cover_page_logo']);
                } elseif (! isset($settings['cover_page_logo'])
                    && isset($this->template->settings['cover_page_logo'])
                ) {
                    $settings['cover_page_logo'] = $this->template->settings['cover_page_logo'];
                }

                foreach (['header', 'footer'] as $headerOrFooter) {
                    for ($i = 1; $i <= 3; ++$i) {
                        $type = "{$headerOrFooter}_column{$i}_type";

                        if ($settings[$type] === 'image') {
                            $value = "{$headerOrFooter}_column{$i}_value";

                            if (! isset($settings[$value])
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
        } catch (\Exception $e) {
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

                if ($this->template !== null
                    && $this->template->settings[$type] === 'image'
                    && isset($this->template->settings[$value])
                ) {
                    $this->add(Html::tag(
                        'p',
                        ['style' => ['margin-left: 15em;']],
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
                        'page_number'           => 'Page Number',
                        'total_number_of_pages' => 'Total Number of Pages',
                        'page_of'               => 'Page Number + Total Number of Pages',
                        'date'                  => 'Date'
                    ],
                    'value' => 'report_title'
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
