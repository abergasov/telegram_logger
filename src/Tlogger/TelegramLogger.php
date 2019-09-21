<?php

namespace Tlogger;
use InvalidArgumentException;
use Exception;
use Throwable;
use CURLFile;

class TelegramLogger {

    private $token = null;
    private $chatTarget = null;
    private $logPath = null;
    private $decorateUrl = null;
    private $logs = [];

    private $traceFile = null;
    private $traceParams = [];

    /**
     * TelegramLogger constructor.
     * @param array $config settings, see description at https://github.com/abergasov/telegram_logger
     */
    public function __construct($config) {
        if (!is_array($config)) {
            throw new InvalidArgumentException('Config should be string');
        }
        $token = $config['token'];
        if (!is_string($token)) {
            throw new InvalidArgumentException('Token should be string');
        }
        $this->token = $token;

        $chatData = $config['chats'];
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

        $createTelegramLog = isset($config['trace_dir']) ? $config['trace_dir'] : false;
        if (is_bool($createTelegramLog)) {
            $this->decorateUrl = '';
        } else {
            if (!is_dir($createTelegramLog)) throw new InvalidArgumentException('Trace path does not exist');
            if (!is_writable($createTelegramLog)) throw new InvalidArgumentException('Trace path is not writable');
        }
        $this->decorateUrl = isset($config['decorate_url']) ? $config['decorate_url'] : '';
        $this->logPath = $createTelegramLog;
        if (isset($config['logs']) && is_array($config['logs'])) {
            if (file_exists($config['logs']['access_log'])) {
                if (!is_readable($config['logs']['access_log'])) throw new InvalidArgumentException('Access log is not readable');

                $this->logs['access_log'] = $config['logs']['access_log'];
            }
            if (file_exists($config['logs']['error_log'])) {
                if (!is_readable($config['logs']['error_log'])) throw new InvalidArgumentException('Error log is not readable');

                $this->logs['error_log'] = $config['logs']['error_log'];
            }
        }
    }

    /**
     * Send message via telegram
     * If $data will contains exception, stack trace log will be created
     * @param $chat
     * @param mixed ...$data
     * @return bool
     */
    public function sendMessage ($chat, ...$data) {
        if (!isset($this->chatTarget[$chat])) {
            throw new InvalidArgumentException('Chat not found in chat data');
        }
        if (count($data) === 0) {
            throw new InvalidArgumentException('Message data can\'t be emty');
        }
        $this->traceFile = null;
        $preparedMessage = implode("\n", $this->transformData(...$data));
        $preparedMessage = mb_convert_encoding(strip_tags($preparedMessage), "UTF-8");
        $preparedMessage = mb_substr($preparedMessage, 0, 3900);
        if (is_null($this->traceFile)) {
            $requestResult = $this->sendTelegramRequest([
                'text' => $preparedMessage,
                'chat_id' => $this->chatTarget[$chat]
            ]);
        } else {
            $requestResult = $this->sendTelegramRequest([
                'caption' => substr($preparedMessage,0,1020) . '...',
                'chat_id' => $this->chatTarget[$chat],
                'document' => new CURLFile(realpath($this->traceFile)),
            ], true);
        }
        $this->traceFile = null;

        $response = json_decode($requestResult, true);
        return is_array($response) && isset($response['ok']) ? $response['ok'] : false;
    }

    /**
     * Add params to trace log
     * @param string $paramTitle
     * @param $paramValue
     */
    public function addTraceParam ($paramTitle, $paramValue) {
        $this->traceParams[$paramTitle] = $paramValue;
    }

    private function transformData (...$data) {
        $result = [];
        foreach ($data as $datum) {
            if ($datum instanceof Exception || $datum instanceof Throwable) {
                $traceFile = $this->createTraceLogFile($datum);
                $result[] = implode("\n", [
                    $datum->getMessage() . ', code:' .  $datum->getCode(),
                    $datum->getFile() . ':' . $datum->getLine()
                ]);
                if ($this->decorateUrl !== '' && is_string($this->logPath)) {
                    $result[] = 'Request info:' . $this->decorateUrl . '/' . $traceFile;
                } elseif ($this->logPath === true) {
                    $this->traceFile = $traceFile;
                }
                continue;
            }
            switch (gettype($datum)) {
                case 'array':
                    $result[] = $this->implodeAll("\n", $datum);
                    break;
                case 'object':
                    $result[] = substr(serialize($datum), 0, 200);
                    break;
                default:
                    $result[] = $datum;
            }
        }
        return $result;
    }

    private function implodeAll($glue, $arr){
        foreach ($arr as $i => &$el) {
            if (is_array($el)) {
                $el = $this->implodeAll ($glue, $el);
            }
        }
        return implode($glue, $arr);
    }

    private function createTraceLogFile ($e) {
        $fileName = time() . '_' . rand(100, 1000) . '.txt';
        if (is_string($this->logPath)) {
            $filePath = $this->logPath . DIRECTORY_SEPARATOR . $fileName;
        } elseif ($this->logPath === true) {
            $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        } else {
            return null;
        }

        $logData = [];
        $logData[] = 'Method: ' . filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);

        $logData[] = '$_GET = ' . var_export(filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING),true);
        $logData[] = '$_POST = ' . var_export(filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING),true);
        $logData[] = $e;
        $logData[] = 'php://input = ' . var_export(file_get_contents('php://input'),true);

        if (count($this->traceParams) > 0) {
            foreach ($this->traceParams as $paramName => $paramValue) {
                $logData[] = $fileName . ' = ' . var_export($paramValue,true);
            }
        }

        $logData[] = '$_COOKIE = ' . var_export(filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_STRING),true);
        $logData[] = '$_ENV = ' . var_export(filter_input_array(INPUT_ENV, FILTER_SANITIZE_STRING),true);

        if (isset($_FILES)) {
            $logData[] = '$_FILES = ' . var_export(filter_var_array($_FILES, FILTER_SANITIZE_STRING),true);
        }
        if (isset($_SERVER)) {
            $logData[] = '$_SERVER = ' . var_export(filter_input_array(INPUT_SERVER, FILTER_SANITIZE_STRING), true);
        }

        if (!empty($this->logs) && isset($this->logs['access_log'])) {
            $logData[] = 'access_log = ' . var_export($this->parseLogFile('access_log'), true);
        }
        if (!empty($this->logs) && isset($this->logs['error_log'])) {
            $logData[] = 'error_log = ' . var_export($this->parseLogFile('error_log'), true);
        }

        //todo additional info into logs via GLOBALS vars
        /*foreach (['ql_errors', 'ql_response_headers', 'ql_request'] as $logType) {
            if (isset($GLOBALS[$logType])) {
                $logData[] = $logType . ' = ' . var_export($GLOBALS[$logType], true);
                unset($GLOBALS[$logType]);
            }
        }*/

        file_put_contents($filePath, "\xEF\xBB\xBF" . implode("\n\n", $logData));
        return $fileName;
    }

    private function  parseLogFile ($logFile) {
        $logFile = $this->logs[$logFile];

        if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $iterator = $this->readTheFile($logFile);

        $buffer = [];
        foreach ($iterator as $iteration) {
            $position = strpos($iteration, $ip);
            if ($position === false) {
                continue;
            }
            if (count($buffer) === 50) {
                array_shift($buffer);
            }
            $buffer[] = $iteration;
        }
        return $buffer;
    }

    private function readTheFile($path) {
        $handle = fopen($path, "r");
        try {
            while(!feof($handle)) {
                yield trim(fgets($handle));
            }
        } finally {
            fclose($handle);
        }
    }

    private function sendTelegramRequest ($data, $sendFile = false) {
        if (!is_array($data) || count($data) === 0) {
            throw new InvalidArgumentException('Message info array expected');
        }
        $url = $sendFile ?
            'https://api.telegram.org/bot' . $this->token . '/sendDocument?chat_id=' . $data['chat_id'] :
            'https://api.telegram.org/bot' . $this->token . '/sendMessage';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function __get($name) {
        if (in_array($name, ['token', 'chatTarget', 'logPath', 'decorateUrl', 'logs'])) {
            return $this->$name;
        } else {
            return null;
        }
    }
}