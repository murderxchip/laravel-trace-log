<?php
/**
 * Created by PhpStorm.
 * User: qinming
 * Date: 2019/4/2
 * Time: 下午6:19
 */


$_SERVER['HTTP_HOST'] = 'localhostwww';
$_SERVER['traceIndex'] = '0.1';
$_SERVER['traceId'] = 'traceId:12312323335';
$_SERVER['tracePath'] = '/ParentPath';

require dirname(__FILE__).'/../../vendor/autoload.php';
require dirname(__FILE__).'/../common/RemoteCall.php';
require dirname(__FILE__) . '/../HttpClient.php';



$client = new \Cactus\Trace\HttpClient();
\Cactus\Trace\HttpClient::setDebug(true);
$response = $client->request('GET', 'http://testserver.test/server.php');

var_dump($response->getBody()->getContents());
