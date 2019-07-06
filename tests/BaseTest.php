<?php

use PHPUnit\Framework\TestCase;
use Tlogger\TelegramLogger;

class BaseTest extends TestCase {

    public function testConfigLoader () {
        $token = '123';
        $chatArray = [123];
        $logger = new TelegramLogger($token, $chatArray);
        $this->assertEquals($logger->token, $token);

        //check target chat configured
        $logger = new TelegramLogger($token, $chatArray);
        $chatTarget = $logger->chatTarget;
        $this->assertIsArray($chatTarget);
        $this->assertTrue(count($chatTarget) > 0);
        $chatArray = [
            0 => 123,
            1 => 222
        ];
        $logger = new TelegramLogger($token, $chatArray);
        $chatTarget = $logger->chatTarget;
        $this->assertIsArray($chatTarget);
        $this->assertTrue(count($chatTarget) > 0);

        //check path is validated
        $createTelegramLog = false; //do not create file with trace logs
        $logger = new TelegramLogger($token, $chatArray, $createTelegramLog);
        $this->assertFalse($logger->logPath);

        $createTelegramLog = true; //create file with trace logs
        $logger = new TelegramLogger($token, $chatArray, $createTelegramLog);
        $this->assertTrue($logger->logPath);

        $createTelegramLog = __DIR__; //create file with trace logs
        $decorateUrl = '';
        $logger = new TelegramLogger($token, $chatArray, $createTelegramLog, $decorateUrl);
        $this->assertIsString($logger->logPath);
        $this->assertIsString($logger->decorateUrl);
        $this->assertTrue(strlen($logger->decorateUrl) === 0);

        $createTelegramLog = __DIR__; //create file with trace logs
        $decorateUrl = 'asdsadsa';
        $logger = new TelegramLogger($token, $chatArray, $createTelegramLog, $decorateUrl);
        $this->assertIsString($logger->logPath);
        $this->assertIsString($logger->decorateUrl);
        $this->assertTrue(strlen($logger->decorateUrl) === strlen($decorateUrl));
    }

    public function testWrongTokenType () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token should be string');
        new TelegramLogger(123, 123);
    }

    public function testWrongChatDataType () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Chat data should be an integer or array of integers');
        new TelegramLogger('123', 'test');
    }

    public function testMultidimensionArrays () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Multidimensional arrays not supported for chat data');
        new TelegramLogger('123', [[123]]);
    }

    public function testDirPathExist () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Trace path does not exist');
        new TelegramLogger('123', [123], 'test strnig');
    }

    public function testDirPathWritable () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Trace path is not writable');
        new TelegramLogger('123', [123], '/root');
    }
}