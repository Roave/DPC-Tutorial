<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'blog' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/blog',
                    'defaults' => array(
                        'controller' => 'Blog\Controller\Blog',
                        'action' => 'index',
                    ),
                ),
            ),
            'post' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/blog/:slug',
                    'defaults' => [
                        'controller' => 'Blog\Controller\Blog',
                        'action' => 'post',
                    ],
                ],
            ],
            'admin' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/blog/admin',
                    'defaults' => [
                        'controller' => 'Blog\Controller\Admin',
                        'action' => 'index',
                    ],
                ],
            ],
        ),
    ),
    'service_manager' => array(),
    'controllers' => array(),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'template_map' => array(),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(),
        ),
    ),
);
