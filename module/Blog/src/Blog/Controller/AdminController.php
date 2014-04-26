<?php
/**
 * User: garyhockin
 * Date: 17/04/2014
 * Time: 15:17
 */

namespace Blog\Controller;

use Blog\Service\PostService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    /**
     * @var PostService
     */
    protected $postService;

    function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function indexAction()
    {
        $posts = $this->postService->getLatestPosts(100);
        return new ViewModel();
    }
} 