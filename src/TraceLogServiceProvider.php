<?php
/**
 * Created by PhpStorm.
 * User: qinming
 * Date: 2019/3/29
 * Time: 下午1:09
 */

namespace Cactus\Trace;

use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

class TraceLogServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function getLogFilename($prefix = 'line')
    {
        return sprintf('%s-%s-%s.log', $prefix, env('APP_NAME', app('request')->getHost()), env('APP_ENV', app('request')->getHost()));
    }

    public function register()
    {
        $this->app->singleton(TraceLogFactory::CLIENT_NAME, function () {
            return new HttpClient();
        });

        $this->app->singleton(TraceLogFactory::API_LOGGER_NAME, function () {
            $maxFiles   = 20;
            $monolog    = new Logger(TraceLogFactory::API_LOGGER_NAME);
            $handlers   = [];
            $handlers[] = (new RotatingFileHandler(storage_path() . "/logs/api/" . $this->getLogFilename('trace'), $maxFiles))
                ->setFormatter(new JsonFormatter());
            $monolog->setHandlers($handlers);
            $monolog->pushProcessor(new TraceRequestProcessor(TraceLogFactory::getHttpClient()));
            $monolog->pushProcessor(new UidProcessor(24));

            return $monolog;
        });

        if (method_exists($this->app, 'configureMonologUsing')) {
            $this->app->configureMonologUsing(function ($monolog) {
                $maxFiles   = 20;
                $handlers   = [];
                $handlers[] = (new RotatingFileHandler(storage_path() . "/logs/" . $this->getLogFilename('line'), $maxFiles))
                    ->setFormatter(new TraceLineFormatter());

                $monolog->setHandlers($handlers);
                $monolog->pushProcessor(new TraceRequestProcessor(TraceLogFactory::getHttpClient()));
//            $monolog->pushProcessor(new IntrospectionProcessor());

                return $monolog;
            });
        }
    }
}