<?php

namespace Blog\Service;


use Blog\Entity\Post;
use Zend\Db\TableGateway\TableGateway;

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
        if($limit) {
            $sql->limit($limit);
        }

        return $this->postTable->selectWith($sql);
    }
} 