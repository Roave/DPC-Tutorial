<?php
/**
 * User: garyhockin
 * Date: 17/04/2014
 * Time: 15:17
 */

namespace Blog\Controller;

use Blog\Entity\Post;
use Blog\Form\PostForm;
use Blog\Service\PostService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\Hydrator\ClassMethods;
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
        return new ViewModel(['posts' => $posts]);
    }

    public function addAction()
    {
        $form = new PostForm();
        $post = new Post();

        $form->setHydrator(new ClassMethods())->setObject($post);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost()->toArray());
            if($form->isValid()) {
                $this->postService->savePost($form->getObject());
                $this->redirect()->toRoute('admin');
            }
        } else {
            $form->bind($post);
        }

        $form->get('submit')->setValue('Add');

        return new ViewModel(['post' => $post, 'form' => $form]);
    }

    public function editAction()
    {
        $postId = (int)$this->params()->fromRoute('post-id');
        if (!$post = $this->postService->getPostById($postId)) {
            throw new \InvalidArgumentException("Post with id $postId cannot be found");
        }

        $form = new PostForm();
        $form->setHydrator(new ClassMethods())->setObject($post);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()) {
                $this->postService->savePost($form->getObject());
                $this->redirect()->toRoute('admin');
            }
        } else {
            $form->bind($post);
        }


        $form->get('submit')->setValue('Edit');

        return new ViewModel(['post' => $post, 'form' => $form]);
    }
}
