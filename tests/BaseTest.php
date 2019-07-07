<?php

use PHPUnit\Framework\TestCase;
use Tlogger\TelegramLogger;

class BaseTest extends TestCase {

    private function getValidLogger () {
        return new TelegramLogger([
            'token' => '123',
            'chats' => 662342
        ]);
    }

    public function testConfigLoader () {
        $token = '123';
        $chatArray = [123];
        $config = [
            'token' => '123',
            'chats' => [123]
        ];
        $logger = new TelegramLogger($config);
        $this->assertEquals($logger->token, $token);

        //check target chat configured
        $logger = new TelegramLogger($config);
        $chatTarget = $logger->chatTarget;
        $this->assertIsArray($chatTarget);
        $this->assertTrue(count($chatTarget) > 0);

        $config['chats'] = [
            0 => 123,
            1 => 222
        ];
        $logger = new TelegramLogger($config);
        $chatTarget = $logger->chatTarget;
        $this->assertIsArray($chatTarget);
        $this->assertTrue(count($chatTarget) > 0);

        //check path is validated
        $config['trace_dir'] = false;//do not create file with trace logs
        $logger = new TelegramLogger($config);
        $this->assertFalse($logger->logPath);

        $config['trace_dir'] = true;//create file with trace logs
        $logger = new TelegramLogger($config);
        $this->assertTrue($logger->logPath);

        $config['trace_dir'] = __DIR__; //create file with trace logs
        $config['decorate_url'] = '';
        $logger = new TelegramLogger($config);
        $this->assertIsString($logger->logPath);
        $this->assertIsString($logger->decorateUrl);
        $this->assertTrue(strlen($logger->decorateUrl) === 0);

        $createTelegramLog = __DIR__; //create file with trace logs
        $config['decorate_url'] = 'asdsadsa';
        $logger = new TelegramLogger($config);
        $this->assertIsString($logger->logPath);
        $this->assertIsString($logger->decorateUrl);
        $this->assertTrue(strlen($logger->decorateUrl) === strlen($config['decorate_url']));
    }

    public function testDataTransformer () {
        $method = new ReflectionMethod('Tlogger\TelegramLogger', 'transformData');
        $method->setAccessible(true);

        $fancyThing = $this->getValidLogger();

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

    public function testImplodeAll () {
        $method = new ReflectionMethod('Tlogger\TelegramLogger', 'implodeAll');
        $method->setAccessible(true);

        $arr = [
            1,
            2,
            ['232', '33'],
            ['55'],
            [
                ['23'. 55, 'tst str'],
                ['7'. 666, '2 tst str',
                    ['55'],
                    [
                        ['23'. 55, 'tst str'],
                        ['7'. 666, '2 tst str'],
                        555
                    ]
                ],
                555
            ]
        ];

        $fancyThing = $this->getValidLogger();
        $result = $method->invokeArgs($fancyThing, [',', $arr]);
        $this->assertIsString($result);
        $this->assertTrue(strpos($result, 'tst') !== false);
        $this->assertTrue(strpos($result, '232') !== false);
    }

    public function testSendMessage () {
        $logger = $this->getValidLogger();
        $res = $logger->sendMessage(0, 'test message', 'testSendMessage function');
        $this->assertIsBool($res);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Chat not found in chat data');
        $logger->sendMessage(123, 'test message', 'testSendMessage function');
    }

    public function testSendMessageEmptyData () {
        $logger = $this->getValidLogger();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message data can\'t be emty');
        $logger->sendMessage(0);
    }

    public function testCreateTraceLogFile () {
        $method = new ReflectionMethod('Tlogger\TelegramLogger', 'createTraceLogFile');
        $method->setAccessible(true);
        $fancyThing = new TelegramLogger([
            'token' => '123',
            'chats' => 662342,
            'trace_dir' => false
        ]);
        $result = $method->invoke($fancyThing, '');
        $this->assertNull($result);

        $fancyThing = new TelegramLogger([
            'token' => '123',
            'chats' => 662342,
            'trace_dir' => __DIR__
        ]);
        $result = $method->invoke($fancyThing, new Exception ('test exception'));
        $this->assertIsString($result);
        $this->assertTrue(file_exists(__DIR__  . DIRECTORY_SEPARATOR . $result));
        $this->assertTrue(filesize(__DIR__ . DIRECTORY_SEPARATOR . $result) > 0);
    }

    public function testSendTelegramRequest () {
        $method = new ReflectionMethod('Tlogger\TelegramLogger', 'sendTelegramRequest');
        $method->setAccessible(true);
        $fancyThing = new TelegramLogger([
            'token' => '123',
            'chats' => 662342,
            'trace_dir' => __DIR__
        ]);
        $result = $method->invoke($fancyThing, ['123' => '22']);
        $data = json_decode($result, true);
        $this->assertTrue(isset($data['ok']));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message info array expected');
        $method->invoke($fancyThing, '123213');
    }

    public function testWrongTokenType () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token should be string');
        new TelegramLogger([
            'token' => 123,
            'chats' => 662342,
        ]);
    }

    public function testWrongChatDataType () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Chat data should be an integer or array of integers');
        new TelegramLogger([
            'token' => '123',
            'chats' => 'test',
            'trace_dir' => '/root'
        ]);

    }

    public function testMultidimensionArrays () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Multidimensional arrays not supported for chat data');
        new TelegramLogger([
            'token' => '123',
            'chats' => [[123]],
            'trace_dir' => '/root'
        ], );
    }

    public function testDirPathExist () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Trace path does not exist');
        new TelegramLogger([
            'token' => '123',
            'chats' => 662342,
            'trace_dir' => '/test_dir_not_exist'
        ]);
    }

    public function testDirPathWritable () {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Trace path is not writable');
        new TelegramLogger([
            'token' => '123',
            'chats' => 662342,
            'trace_dir' => '/root'
        ]);
    }
}