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

    public function testDataTransformer () {
        $method = new ReflectionMethod('Tlogger\TelegramLogger', 'transformData');
        $method->setAccessible(true);

        $fancyThing = new TelegramLogger('test', 123);

        $args = ['123', [123, 323], [123, 323, [33, 55]], new Exception('test exception'), new stdClass()];
        foreach ($args as $arg) {
            $result = $method->invokeArgs($fancyThing, [$arg, $arg, $arg]);
            $this->assertIsArray($result);
            $this->assertTrue(count($result) > 0);
            foreach ($result as $res) {
                $this->assertTrue(!is_array($res));
            }
        }
    }

    public function testSendMessage () {
        $logger = new TelegramLogger('123', [123456]);
        $res = $logger->sendMessage(0, 'test message', 'testSendMessage function');
        $this->assertIsBool($res);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Chat not found in chat data');
        $logger->sendMessage(123, 'test message', 'testSendMessage function');
    }

    public function testSendMessageEmptyData () {
        $logger = new TelegramLogger('123', [123456]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message data can\'t be emty');
        $logger->sendMessage(123);
    }

    public function testCreateTraceLogFile () {
        $method = new ReflectionMethod('Tlogger\TelegramLogger', 'createTraceLogFile');
        $method->setAccessible(true);
        $fancyThing = new TelegramLogger('test', 123, __DIR__);
        $result = $method->invoke($fancyThing, '');
        $this->assertNull($result);

        $result = $method->invoke($fancyThing, new Exception ('test exception'));
        $this->assertIsString($result);
        $this->assertTrue(file_exists(__DIR__  . $result));
        $this->assertTrue(filesize(__DIR__ . $result) > 0);
    }

    public function testSendTelegramRequest () {
        $method = new ReflectionMethod('Tlogger\TelegramLogger', 'sendTelegramRequest');
        $method->setAccessible(true);
        $fancyThing = new TelegramLogger('test', 123, __DIR__);
        $result = $method->invoke($fancyThing, []);
        $data = json_decode($result);
        $this->assertTrue(isset($data['ok']));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message info array expected');
        $method->invoke($fancyThing, '123213');
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