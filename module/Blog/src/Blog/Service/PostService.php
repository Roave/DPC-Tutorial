<?php

namespace Blog\Service;

use Blog\Entity\Post;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ClassMethods;

class PostService
{
    /**
     * @var TableGateway
     */
    protected $postTable;

    function __construct(TableGateway $postTable)
    {
        $this->postTable = $postTable;
    }

    /**
     * @param null $limit
     * @return null|Post
     * @throws \Zend\Db\Sql\Exception\InvalidArgumentException
     */
    public function getLatestPosts($limit = null)
    {
        $sql = $this->postTable->getSql()->select()
            ->order('written_on DESC');
        if ($limit) {
            $sql->limit($limit);
        }

        return $this->postTable->selectWith($sql);
    }

    /**
     * @param $slug
     * @return Post
     */
    public function getPostBySlug($slug)
    {
        return $this->postTable->select(
            [
                'slug' => $slug
            ]
        )->current();
    }

    /**
     * @param $postId
     * @return bool|Post
     */
    public function getPostById($postId)
    {
        $rowSet = $this->postTable->select(['id' => $postId]);
        return $rowSet->current();
    }

    /**
     * @param Post $post
     * @return bool
     */
    public function savePost(Post $post)
    {
        if(is_null($post->getId())) {
            $post->setWrittenOn(new Expression('NOW()'));
            $post->setViews(0);
        }
        $hydrator = new ClassMethods();
        $postArray = $hydrator->extract($post);

        if($this->getPostById($post->getId())) {
            $this->postTable->update($postArray, ['id' => $post->getId()]);
            return true;
        }

        $this->postTable->insert($postArray);
        return true;
    }

    public function incrementViewBySlug($slug)
    {
        if(!$post = $this->getPostBySlug($slug)) {
            throw new \InvalidArgumentException('Post not found');
        }
        $post->setViews($post->getViews() + 1);
        $this->savePost($post);
    }
}