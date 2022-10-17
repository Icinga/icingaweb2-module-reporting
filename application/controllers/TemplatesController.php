<?php

// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TemplateForm;
use Icinga\Module\Reporting\Web\ReportsTimeframesAndTemplatesTabs;
use ipl\Html\Html;
use ipl\Sql\Select;
use ipl\Web\Url;
use ipl\Web\Widget\ButtonLink;
use ipl\Web\Widget\Link;

class TemplatesController extends Controller
{
    use Database;
    use ReportsTimeframesAndTemplatesTabs;

    public function indexAction()
    {
        $this->createTabs()->activate('templates');

        $canManage = $this->hasPermission('reporting/templates');

        if ($canManage) {
            $this->addControl(new ButtonLink(
                $this->translate('New Template'),
                Url::fromPath('reporting/templates/new'),
                'plus'
            ));
        }

        $select = (new Select())
            ->from('template')
            ->columns(['id', 'name', 'author', 'ctime', 'mtime'])
            ->orderBy('mtime', SORT_DESC);

        foreach ($this->getDb()->select($select) as $template) {
            if ($canManage) {
                // Edit URL
                $subjectUrl = Url::fromPath(
                    'reporting/template/edit',
                    ['id' => $template->id]
                );
            } else {
                // Preview URL
                $subjectUrl = Url::fromPath(
                    'reporting/template',
                    ['id' => $template->id]
                );
            }

            $tableRows[] = Html::tag('tr', null, [
                Html::tag('td', null, new Link($template->name, $subjectUrl)),
                Html::tag('td', null, $template->author),
                Html::tag('td', null, date('Y-m-d H:i', $template->ctime / 1000)),
                Html::tag('td', null, date('Y-m-d H:i', $template->mtime / 1000))
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                ['class' => 'common-table table-row-selectable', 'data-base-target' => '_next'],
                [
                    Html::tag(
                        'thead',
                        null,
                        Html::tag(
                            'tr',
                            null,
                            [
                                Html::tag('th', null, 'Name'),
                                Html::tag('th', null, 'Author'),
                                Html::tag('th', null, 'Date Created'),
                                Html::tag('th', null, 'Date Modified')
                            ]
                        )
                    ),
                    Html::tag('tbody', null, $tableRows)
                ]
            );

            $this->addContent($table);
        } else {
            $this->addContent(Html::tag('p', null, 'No templates created yet.'));
        }
    }

    public function newAction()
    {
        $this->assertPermission('reporting/templates');
        $this->addTitleTab('New Template');

        $form = new TemplateForm();

        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/templates');

        $this->addContent($form);
    }
}
