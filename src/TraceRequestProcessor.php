<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cactus\Trace;

use Illuminate\Support\Facades\Auth;
use Monolog\Processor\ProcessorInterface;

/**
 * Injects url/method and remote IP of the current web request in all records
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class TraceRequestProcessor implements ProcessorInterface
{
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['Trace'] = [];
        $record['Trace']['TraceId'] = $this->client->getTraceId();
        $record['Trace']['TraceIndex'] = $this->client->getTraceIndex();
        $record['Trace']['TracePath'] = $this->client->getTracePath();

        $auth = Auth::user();
        if($auth){
            $record['auth'] = method_exists($auth, 'toArray') ? $auth->toArray() : $auth;
        }

        return $record;
    }

}