<?php

namespace Blog\Service;


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

} 