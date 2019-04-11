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
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\UidProcessor;

class TraceLogServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function getLogFilename()
    {
        return sprintf('%s-%s.log', env('APP_NAME', app('request')->getHost()), env('APP_ENV', app('request')->getHost()));
    }

    public function register()
    {
        $this->app->singleton('rpcClient', function () {
            return new HttpClient();
        });

        $this->app->configureMonologUsing(function ($monolog) {
            $maxFiles = 30;
            $handlers = [];
            $handlers[] = (new RotatingFileHandler(storage_path() . "/logs/lumen.log", $maxFiles))
                ->setFormatter(new LineFormatter(null, null, true, true));

            $handlers[] = (new RotatingFileHandler(storage_path() . "/logs/json/" . $this->getLogFilename(), $maxFiles))
                ->setFormatter(new JsonFormatter());
            $monolog->setHandlers($handlers);
            $monolog->pushProcessor(new TraceRequestProcessor(app('rpcClient')));
            $monolog->pushProcessor(new UidProcessor(24));
//            $monolog->pushProcessor(new IntrospectionProcessor());

            return $monolog;
        });
    }
}