<?php

namespace Tlogger;

class TelegramLogger {

    private $token = null;

    public function __construct($token) {
        $this->setConfig();
    }

    private function setConfig () {
        $this->token = '';
    }
}