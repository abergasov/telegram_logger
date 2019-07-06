<?php

use PHPUnit\Framework\TestCase;
use Tlogger\TelegramLogger;

class BaseTest extends TestCase {

    public function testConfigLoader () {

        $logger = new TelegramLogger(2);

        array_push($stack, 'foo');
        $this->assertSame('foo', $stack[count($stack)-1]);
        $this->assertSame(1, count($stack));

        $this->assertSame('foo', array_pop($stack));
        $this->assertSame(0, count($stack));
    }
}