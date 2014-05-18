<?php

namespace SxCoreTest\Html\Exception;

use PHPUnit_Framework_TestCase;
use SxCore\Html\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends PHPUnit_Framework_TestCase
{

    public function testException()
    {
        $exception = new InvalidArgumentException('test');

        $this->assertInstanceOf('\SxCore\Html\Exception\ExceptionInterface', $exception);
        $this->assertInstanceOf('\InvalidArgumentException', $exception);
    }
}
