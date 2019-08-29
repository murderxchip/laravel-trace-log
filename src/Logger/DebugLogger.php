<?php


namespace Cactus\Trace\Logger;


use Cactus\Trace\TraceLineFormatter;
use Cactus\Trace\TraceLogFactory;
use Cactus\Trace\TraceRequestProcessor;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class DebugLogger
{
    public function __invoke()
    {
        $maxFiles = 20;
        $handlers = [];

        $handlers[] = (new RotatingFileHandler(storage_path() . "/logs/" . $this->getLogFilename('line'), $maxFiles))
            ->setFormatter(new TraceLineFormatter());

        $monolog = new Logger('system');
        $monolog->setHandlers($handlers);
        $monolog->pushProcessor(new TraceRequestProcessor(TraceLogFactory::getHttpClient()));

        return $monolog;
    }
}