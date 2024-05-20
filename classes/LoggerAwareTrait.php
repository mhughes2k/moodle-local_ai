<?php

namespace local_ai;

trait LoggerAwareTrait {
    private $logger;
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
    public function log($message, array $context = [], $level = LogLevel::INFO) {
        $this->logger->log($level, $message, $context);
    }
}
