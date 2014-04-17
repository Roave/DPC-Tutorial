<?php
/**
 * User: garyhockin
 * Date: 17/04/2014
 * Time: 15:17
 */

namespace Blog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
} 