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

    /**
     * @var TraceIndex
     */
    static $traceIndex;

    static $tracePath;
    static $tracePathSave = false;

    static $headerTraceId    = 'TRACEID';
    static $headerTraceIndex = 'TRACEINDEX';
    static $headerTracePath  = 'TRACEPATH';

    static $debug = false;

    static $init = false;

    /**
     * @param bool $debug
     */
    public static function setDebug(bool $debug)
    {
        self::$debug = $debug;
    }

    protected function getRequestHeader($header = '', $default = '')
    {
        $header = app('request')->header($header);
        if (!$header) {
            return $default;
        }

        if (is_array($header)) {
            return $header[0] ?? $default;
        }

        if (is_string($header)) {
            return $header;
        }

        return $default;
    }

    public function __construct(array $config = [])
    {
        if (!static::$init) {
            static::$traceId = $this->getRequestHeader(static::$headerTraceId, static::genTraceId());

            $indexHeader     = $this->getRequestHeader(static::$headerTraceIndex, '');
            static::$traceIndex = new TraceIndex($indexHeader);

            static::$tracePath  = $this->getRequestHeader(static::$headerTracePath, ($_SERVER['SERVER_NAME'] ?? 'unknown'));

            if (static::$tracePath === false || !static::$tracePathSave) {
                static::$tracePath     .= '/' . env('APP_NAME', $_SERVER['HTTP_HOST'] ?? 'unknown');
                static::$tracePathSave = true;
            }

            static::$init = true;
        }

        $stack = HandlerStack::create();
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {

            if (static::$debug) {
                echo '[trace] - ' . static::$traceId . ' - ' . static::$traceIndex->getIndex() . ' - ' . static::$tracePath . PHP_EOL;
            }
            return $request->withHeader(static::$headerTraceId, static::$traceId)
                ->withHeader(static::$headerTraceIndex, static::$traceIndex->getIndex())
                ->withHeader(static::$headerTracePath, static::$tracePath);
        }));

        $config['handler'] = $stack;
//        $config['timeout'] = 5;

        parent::__construct($config);
    }

    public function request($method, $uri = '', array $options = [])
    {
        static::$traceIndex->incr();

        return parent::request($method, $uri, $options);
    }

    public static function genTraceId()
    {
        return static::$traceId ?? uniqid('tx' . microtime(true) * 10000);
    }

    /**
     * @return string
     */
    public static function getTraceIndex()
    {
        return self::$traceIndex->getIndex();
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