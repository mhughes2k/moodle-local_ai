<?php

namespace local_ai;

interface LoggerAwareInterface {
    public function setLogger(LoggerInterface $logger);
}
