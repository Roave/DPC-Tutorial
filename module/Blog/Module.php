<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Blog;

use Blog\Controller\BlogController;
use Blog\Controller\AdminController;
use Blog\Entity\Post;
use Blog\Service\PostService;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\MvcEvent;
use Zend\Navigation\Page\Mvc;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'postRoute'], -100);
    }

    public function postRoute(MvcEvent $e)
    {
        if($slug = $e->getRouteMatch()->getParam('slug')) {
            /** @var PostService $postService */
            $postService = $e->getApplication()->getServiceManager()->get('Blog\Service\PostService');
            $postService->incrementViewBySlug($slug);
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }


    public function getControllerConfig()
    {
        return [
            'factories' => [
                'Blog\Controller\Blog' => function(ControllerManager $controllerManager) {
                    $postService = $controllerManager->getServiceLocator()->get('Blog\Service\PostService');
                    return new BlogController($postService);
                },
                'Blog\Controller\Admin' => function(ControllerManager $controllerManager) {
                    $postService = $controllerManager->getServiceLocator()->get('Blog\Service\PostService');
                    return new AdminController($postService);
                }
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'PostTableGateway' => function (ServiceManager $serviceManager) {
                    $adapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $hydrator           = new ClassMethods(true);
                    $rowObjectPrototype = new Post();
                    $resultSet          = new HydratingResultSet($hydrator, $rowObjectPrototype);
                    $tableGateway       = new TableGateway('post', $adapter, null, $resultSet);
                    return $tableGateway;
                },
                'Blog\Service\PostService' => function(ServiceManager $serviceManager) {
                    $postTable = $serviceManager->get('PostTableGateway');
                    return new PostService($postTable);
                },
            ],
        ];
    }
}
