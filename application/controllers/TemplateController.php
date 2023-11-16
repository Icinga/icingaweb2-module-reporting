<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use DateTime;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Model;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TemplateForm;
use Icinga\Module\Reporting\Web\Widget\Template;
use Icinga\Web\Notification;
use ipl\Html\Form;
use ipl\Html\ValidHtml;
use ipl\Stdlib\Filter;
use ipl\Web\Url;
use ipl\Web\Widget\ActionBar;
use ipl\Web\Widget\ActionLink;

class TemplateController extends Controller
{
    /** @var Model\Template */
    protected $template;

    public function init()
    {
        parent::init();

        /** @var Model\Template $template */
        $template = Model\Template::on(Database::get())
            ->filter(Filter::equal('id', $this->params->getRequired('id')))
            ->first();

        if ($template === null) {
            throw new Exception('Template not found');
        }

        $this->template = $template;
    }

    public function indexAction()
    {
        $this->addTitleTab($this->translate('Preview'));

        $this->controls->getAttributes()->add('class', 'default-layout');
        $this->addControl($this->createActionBars());

        $template = Template::fromModel($this->template)
            ->setMacros([
                'date'                => (new DateTime())->format('jS M, Y'),
                'time_frame'          => 'Time Frame',
                'time_frame_absolute' => 'Time Frame (absolute)',
                'title'               => 'Icinga Report Preview'
            ])
            ->setPreview(true);

        $this->addContent($template);
    }

    public function editAction()
    {
        $this->assertPermission('reporting/templates');
        $this->addTitleTab($this->translate('Edit Template'));

        $form = TemplateForm::fromTemplate($this->template)
            ->setAction((string) Url::fromRequest())
            ->on(TemplateForm::ON_SUCCESS, function (Form $form) {
                $pressedButton = $form->getPressedSubmitElement();
                if ($pressedButton && $pressedButton->getName() === 'remove') {
                    Notification::success($this->translate('Removed template successfully'));

                    $this->switchToSingleColumnLayout();
                } else {
                    Notification::success($this->translate('Updated template successfully'));

                    $this->closeModalAndRefreshRemainingViews(
                        Url::fromPath('reporting/template', ['id' => $this->template->id])
                    );
                }
            })
            ->handleRequest(ServerRequest::fromGlobals());

        $this->addContent($form);
    }

    protected function createActionBars(): ValidHtml
    {
        $actions = new ActionBar();
        $actions->addHtml(
            (new ActionLink(
                $this->translate('Modify'),
                Url::fromPath('reporting/template/edit', ['id' => $this->template->id]),
                'edit'
            ))->openInModal()
        );

        return $actions;
    }
}
