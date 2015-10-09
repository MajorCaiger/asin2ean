<?php

/**
 * Index Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Application\Controller;

use Application\Service\Amazon;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Index Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $query = (array)$this->getRequest()->getQuery();
        $object = null;

        if (isset($query['id']) && isset($query['type'])) {
            $object = $this->asinLookup($query['id'], $query['type']);
        }

        $view = new ViewModel(['query' => $query, 'object' => $object]);
        $view->setTemplate('pages/index');

        return $view;
    }

    protected function getConfig()
    {
        return $this->getServiceLocator()->get('Config')['amazon'];
    }

    protected function getAccessKey()
    {
        return $this->getConfig()['access_key'];
    }

    protected function getSecret()
    {
        return $this->getConfig()['secret'];
    }

    protected function getTag()
    {
        return $this->getConfig()['tracking_id'];
    }

    protected function asinLookup($id, $type)
    {
        $amazon = new Amazon($this->getAccessKey(), $this->getSecret(), $this->getTag());

        return $amazon->search($id, $type);
    }
}
