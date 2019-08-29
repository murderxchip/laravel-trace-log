<?php


namespace Cactus\Trace;


use GuzzleHttp\Client;

class TraceLogFactory
{
    const CLIENT_NAME = 'rpcClient';
    const API_LOGGER_NAME = 'apiLogger';

    /**
     * @return HttpClient|Client
     */
    public static function getHttpClient()
    {
        return app(self::CLIENT_NAME) ?? new Client();
    }

    public  static function getApiLogger()
    {
        return app('apiLogger');
    }
}