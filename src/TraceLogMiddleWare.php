<?php

namespace Cactus\Trace;

use Carbon\Carbon;
use Closure;

class TraceLogMiddleWare
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $start_time = microtime(true);

        $log = [
//                'TraceId' => $client->getTraceId(),
//                'TraceIndex' => $client->getTraceIndex(),
//                'TracePath' => $client->getTracePath(),
            'Host' => $request->getHttpHost(),
            'Method' => strtoupper($request->method()),
            'Headers' => json_encode($request->headers->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'PathInfo' => $request->getPathInfo(),
            'QueryString' => $request->getQueryString(),
            'Protocol' => $request->getProtocolVersion(),
            'IP' => $request->ip(),
            'User-Agent' => $request->userAgent(),
            'Params' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'RequestUri' => $request->getRequestUri(),
            'RequestTime' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $response = $next($request);

        $end_time = microtime(true);
        $elapse = $end_time - $start_time;
        $elapseTime = intval($elapse * 1000);

        $responseText = $response->getContent();
        $responseJson = @json_decode($response->getContent(), true);

        if (isset($responseJson['debug'])) {
            $log['debug'] = $responseJson['debug'];
            $log['Response'] = $responseJson['message'];
        } else {
            $log['Response'] = $responseText;
        }

        $log['Status'] = $response->getStatusCode();
        $log['ResponseTime'] = Carbon::now()->format('Y-m-d H:i:s');
        $log['ElapseTime'] = $elapseTime;
//        $log['ElapseMS'] = $elapseTime;

        if(is_object(app('apiLogger'))){
            app('apiLogger')->info('apiLogger', $log);
        }
        return $response;
    }
}