<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use DateTime;
use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TemplateForm;
use Icinga\Module\Reporting\Web\Widget\Template;
use ipl\Sql\Select;

class TemplateController extends Controller
{
    use Database;

    public function indexAction()
    {
        $this->createTabs()->activate('preview');

        $template = Template::fromDb($this->params->getRequired('id'));

        if ($template === null) {
            throw new \Exception('Template not found');
        }

        $template
            ->setMacros([
                'date'       => (new DateTime())->format('jS M, Y'),
                'time_frame' => 'Time Frame',
                'title'      => 'Icinga Report Preview'
            ])
            ->setPreview(true);

        $this->addContent($template);
    }

    public function editAction()
    {
        $this->createTabs()->activate('edit');

        $select = (new Select())
            ->from('template')
            ->columns(['id', 'settings'])
            ->where(['id = ?' => $this->params->getRequired('id')]);

        $template = $this->getDb()->select($select)->fetch();

        if ($template === false) {
            throw new \Exception('Template not found');
        }

        $template->settings = json_decode($template->settings, true);

        $form = (new TemplateForm())
            ->setTemplate($template);

        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/templates');

        $this->addContent($form);
    }

    protected function createTabs()
    {
        $tabs = $this->getTabs();

        $tabs->add('edit', [
            'title'     => $this->translate('Edit template'),
            'label'     => $this->translate('Edit Template'),
            'url'       => 'reporting/template/edit?id=' . $this->params->getRequired('id')
        ]);

        $tabs->add('preview', [
            'title'     => $this->translate('Preview template'),
            'label'     => $this->translate('Preview'),
            'url'       => 'reporting/template?id=' . $this->params->getRequired('id')
        ]);

        return $tabs;
    }
}
