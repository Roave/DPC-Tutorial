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

    public function postAction()
    {
        $slug = $this->params()->fromRoute('slug');
        $post = $this->postService->getPostBySlug($slug);
        if (!$post) {
            $this->getResponse()->setStatusCode(404);
            return false;
        }
        return new ViewModel(
            [
                'post' => $post
            ]
        );
    }
}
