<?php
/**
 * Created by PhpStorm.
 * User: qinming
 * Date: 2019/3/29
 * Time: 下午4:17
 */

namespace Cactus\Trace;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class HttpClient extends Client
{
    static $indexCounter = 1;
    static $traceId;
    static $traceIndex;
    static $tracePath;
    static $tracePathSave = false;

    static $headerTraceId = 'TRACEID';
    static $headerTraceIndex = 'TRACEINDEX';
    static $headerTracePath = 'TRACEPATH';

    static $debug = false;

    static $init = false;

    /**
     * @param bool $debug
     */
    public static function setDebug(bool $debug): void
    {
        self::$debug = $debug;
    }

    public function __construct(array $config = [])
    {
        if(!static::$init) {
            static::$traceId = app('request')->header(static::$headerTraceId) ?? static::genTraceId();
            $indexHeader = app('request')->header(static::$headerTraceIndex) ?? '0';
            static::$traceIndex = static::getNextIndex($indexHeader);
            static::$tracePath = app('request')->header(static::$headerTracePath) ?? ($_SERVER['SERVER_NAME'] ?? 'Unknown');

            if (static::$tracePath === false || !static::$tracePathSave) {
                static::$tracePath .= '/' . env('APP_NAME', $_SERVER['HTTP_HOST'] ?? 'unknown');
                static::$tracePathSave = true;
            }

            static::$init = true;
        }

        $stack = HandlerStack::create();
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {

            if (static::$debug) {
                echo '[trace] - ' . static::$traceId . ' - ' . static::$traceIndex . ' - ' . static::$tracePath . PHP_EOL;
            }
            return $request->withHeader(static::$headerTraceId, static::$traceId)
                ->withHeader(static::$headerTraceIndex, static::$traceIndex)
                ->withHeader(static::$headerTracePath, static::$tracePath);
        }));

        $config['handler'] = $stack;
//        $config['timeout'] = 5;

        parent::__construct($config);
    }

    public static function genTraceId()
    {
        return static::$traceId ?? uniqid('trace:' . time());
    }

    /**
     * @return mixed
     */
    public static function getTraceIndex()
    {
        return self::$traceIndex;
    }

    public static function getNextIndex($index = '0')
    {
        $ret = $index . '.' . static::$indexCounter;
        static::$indexCounter++;

        return $ret;
    }

    /**
     * @return mixed
     */
    public static function getTraceId()
    {
        return self::$traceId;
    }

    /**
     * @return mixed
     */
    public static function getTracePath()
    {
        return self::$tracePath;
    }

}