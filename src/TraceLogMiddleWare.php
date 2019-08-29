<?php

namespace Cactus\Trace;

use Carbon\Carbon;
use Closure;

class TraceLogMiddleWare
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $start_time = microtime(true);

        $log = [
            'Host'        => $request->getHttpHost(),
            'Method'      => strtoupper($request->method()),
            'Headers'     => json_encode($request->headers->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'PathInfo'    => $request->getPathInfo(),
            'QueryString' => $request->getQueryString(),
            'Protocol'    => $request->getProtocolVersion(),
            'IP'          => $request->ip(),
            'User-Agent'  => $request->userAgent(),
            'Params'      => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'RequestUri'  => $request->getRequestUri(),
            'RequestTime' => Carbon::now()->format('Y-m-d H:i:s'),
        ];


        $response = $next($request);

        $end_time   = microtime(true);
        $elapse     = $end_time - $start_time;
        $elapseTime = intval($elapse * 1000);

        $responseText = $response->getContent();
        $responseJson = @json_decode($response->getContent(), true);

        if (isset($responseJson['debug'])) {
            $log['debug']    = $responseJson['debug'];
            $log['Response'] = $responseJson['message'];
        } else {
            $log['Response'] = $responseText;
        }

        $log['Status']       = $response->getStatusCode();
        $log['ResponseTime'] = Carbon::now()->format('Y-m-d H:i:s');
        $log['ElapseTime']   = $elapseTime;

        $log['Sql'] = isset($GLOBALS['sql_all']) && is_array($GLOBALS['sql_all']) ? json_encode($GLOBALS['sql_all']) : '';

        if (is_object(app(TraceLogFactory::API_LOGGER_NAME))) {
            app(TraceLogFactory::API_LOGGER_NAME)->info(TraceLogFactory::API_LOGGER_NAME, $log);
        }

        //traceid返回
        if (method_exists($response, 'getContent')) {
            $responseData = @json_decode($response->getContent());
            $responseData->trace = (object)['traceId' => TraceLogFactory::getHttpClient()::getTraceId()];
            $response->setContent(json_encode($responseData));
        }

        return $response;
    }
}