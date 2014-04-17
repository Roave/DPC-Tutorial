<?php

namespace Blog\Controller;

use Blog\Service\PostService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BlogController extends AbstractActionController
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
        $posts = $this->postService->getLatestPosts(10);
        return new ViewModel(
            [
                'posts' => $posts
            ]
        );
    }
}
