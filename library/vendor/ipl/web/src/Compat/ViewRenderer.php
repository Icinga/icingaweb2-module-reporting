<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace reportingipl\Web\Compat;

use Zend_Controller_Action_Helper_ViewRenderer as Zf1ViewRenderer;
use Zend_Controller_Action_HelperBroker as Zf1HelperBroker;

class ViewRenderer extends Zf1ViewRenderer
{
    /**
     * Inject the view renderer
     */
    public static function inject()
    {
        /** @var \Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
        $viewRenderer = Zf1HelperBroker::getStaticHelper('ViewRenderer');

        $inject = new static();

        foreach (get_object_vars($viewRenderer) as $property => $value) {
            if ($property === '_inflector') {
                continue;
            }

            $inject->$property = $value;
        }

        Zf1HelperBroker::removeHelper('ViewRenderer');
        Zf1HelperBroker::addHelper($inject);
    }

    /**
     * Render the view w/o using a view script
     *
     * {@inheritdoc}
     */
    public function render($action = null, $name = null, $noController = null)
    {
        $view = $this->view;

        if (($view->controls->isEmpty() && $view->content->isEmpty())
            || $this->getRequest()->getParam('error_handler') !== null
        ) {
            parent::render($action, $name, $noController);

            return;
        }

        if ($name === null) {
            $name = $this->getResponseSegment();
        }

        $this->getResponse()->appendBody(
            $view->controls->render() . "\n" . $view->content->render(),
            $name
        );

        $this->setNoRender();
    }

    public function getName()
    {
        return 'ViewRenderer';
    }
}
