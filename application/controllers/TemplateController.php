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
use ipl\Stdlib\Filter;

class TemplateController extends Controller
{
    use Database;

    public function indexAction()
    {
        $this->createTabs()->activate('preview');

        $template = Model\Template::on($this->getDb())
            ->filter(Filter::equal('id', $this->params->getRequired('id')))
            ->first();

        if ($template === null) {
            throw new Exception('Template not found');
        }

        $template = Template::fromModel($template)
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

        $this->createTabs()->activate('edit');

        $template = Model\Template::on($this->getDb())
            ->filter(Filter::equal('id', $this->params->getRequired('id')))
            ->first();

        if ($template === false) {
            throw new Exception('Template not found');
        }

        $template->settings = json_decode($template->settings, true);

        $form = TemplateForm::fromTemplate($template)
            ->on(TemplateForm::ON_SUCCESS, function () {
                $this->redirectNow('reporting/templates');
            })
            ->handleRequest(ServerRequest::fromGlobals());

        $this->addContent($form);
    }

    protected function createTabs()
    {
        $tabs = $this->getTabs();

        if ($this->hasPermission('reporting/templates')) {
            $tabs->add('edit', [
                'title' => $this->translate('Edit template'),
                'label' => $this->translate('Edit Template'),
                'url'   => 'reporting/template/edit?id=' . $this->params->getRequired('id')
            ]);
        }

        $tabs->add('preview', [
            'title' => $this->translate('Preview template'),
            'label' => $this->translate('Preview'),
            'url'   => 'reporting/template?id=' . $this->params->getRequired('id')
        ]);

        return $tabs;
    }
}
