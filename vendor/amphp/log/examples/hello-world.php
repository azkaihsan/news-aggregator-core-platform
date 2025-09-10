<?php

use Amp\ByteStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Loop;
use Monolog\Logger;

require dirname(__DIR__) . '/vendor/autoload.php';

Loop::run(function () {
    $handler = new StreamHandler(ByteStream\getStdout());
    $handler->setFormatter(new ConsoleFormatter);

    $logger = new Logger('hello-world');
    $logger->pushHandler($handler);

    $logger->debug("Hello, world!");
    $logger->info("Hello, world!");
    $logger->notice("Hello, world!");
    $logger->error("Hello, world!");
    $logger->alert("Hello, world!");
});
