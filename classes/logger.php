<?php
namespace local_ai;
use local_ai\LogLevel;

class logger implements LoggerInterface {
    protected $logpath;
    function __construct($identifier) {
        $logdir = make_temp_directory('ai', true);
        $this->logpath = $logdir . '/' . $identifier;
        if (!defined('ALREADY_LOGGING')) {
            define('ALREADY_LOGGING', true);
            $this->write("");
            $this->write("Opening Log file");
        }
    }
    protected function interpolate($message, array $context = []) {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
    protected function write($message) {
        $ts = microtime(true);
        //%d %B %Y, %I:%M %p
        $uts = userdate($ts, '%Y-%M-%d %I:%M:%p');
        $f = fopen($this->logpath, 'a');
        if(flock($f, LOCK_EX | LOCK_NB)) {
//            debugging("Writing to log file: {$this->logpath}");
            fwrite($f, "{$ts} - {$uts} - {$message}\n");
            flock($f, LOCK_UN);
        }
        fclose($f);
    }
    public function emergency($message, array $context = []) {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    public function alert($message, array $context = []) {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    public function critical($message, array $context = []) {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []) {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    public function warning($message, array $context = []) {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    public function notice($message, array $context = []) {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    public function info($message, array $context = []) {
        $this->log(LogLevel::INFO, $message, $context);
    }
    public function debug($message, array $context = []) {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
    public function log($level, $message, array $context = []) {
        $message = $this->interpolate($message, $context);
        $rawmessage = "{$level} - {$message}";
        $this->write($rawmessage);
    }
}
