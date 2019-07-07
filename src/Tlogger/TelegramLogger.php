<?php

namespace Tlogger;
use InvalidArgumentException;

class TelegramLogger {

    private $token = null;
    private $chatTarget = null;
    private $logPath = null;
    private $decorateUrl = null;

    /**
     * TelegramLogger constructor.
     * @param string $token bot token
     * @param mixed $chatData target chat for messages. Or array of chats
     * @param mixed $createTelegramLog should create file with log trace to telegram message. if set log path = trace log will be created there
     * @param string $decorateUrl url to acess to log via internet. for example https://example.com/trace/logs/
     */
    public function __construct($token, $chatData, $createTelegramLog = false, $decorateUrl = '') {
        if (!is_string($token)) {
            throw new InvalidArgumentException('Token should be string');
        }
        $this->token = $token;
        if (is_numeric($chatData)) {
            $this->chatTarget = [$chatData];
        } elseif (is_string($chatData)) {
            $chatData = (int) $chatData;
            if ($chatData === 0) {
                throw new InvalidArgumentException('Chat data should be an integer or array of integers');
            }
            $this->chatTarget = [$chatData];
        } elseif (is_array($chatData)) {
            foreach ($chatData as $v) {
                if (is_array($v)) throw new InvalidArgumentException('Multidimensional arrays not supported for chat data');
            }
            $this->chatTarget = $chatData;
        } else {
            throw new InvalidArgumentException('Chat data should be an integer or array of integers');
        }
        if (is_bool($createTelegramLog)) {
            $this->decorateUrl = '';
        } else {
            if (!is_dir($createTelegramLog)) throw new InvalidArgumentException('Trace path does not exist');
            if (!is_writable($createTelegramLog)) throw new InvalidArgumentException('Trace path is not writable');
        }
        $this->decorateUrl = $decorateUrl;
        $this->logPath = $createTelegramLog;
    }

    private function transformData (...$data) {
        return [];
    }

    public function __get($name) {
        switch ($name) {
            case 'token':
                return $this->token;
            case 'chatTarget':
                return $this->chatTarget;
            case 'logPath':
                return $this->logPath;
            case 'decorateUrl':
                return $this->decorateUrl;
            default:
                return null;
        }
    }
}