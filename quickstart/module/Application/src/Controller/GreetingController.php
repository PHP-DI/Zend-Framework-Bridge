<?php

namespace Application\Controller;

use Application\Service\GreetingServiceInterface;
use DI\Annotation\Inject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class GreetingController
 * @author mfris
 * @package Application\Controller
 */
class GreetingController extends AbstractActionController
{
    /**
     * @Inject()
     * @var GreetingServiceInterface
     */
    protected $greetingService;

    /**
     * @return ViewModel
     */
    public function helloAction()
    {
        $name = $this->getRequest()->getQuery('name', 'anonymous');

        return new ViewModel(['greeting' => $this->greetingService->greet($name)]);
    }
}
