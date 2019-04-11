###Lumen/Laravel 服务链路日志跟踪，采用header传递自定义trace信息方式聚合请求链路日志，本地json结构化存储方便elk采集。

使用方法：

1. 安装
   项目composer.json中引入
   ```json
   "repositories": [
            {
                "type": "git",
                "url": "https://git.100tal.com/jituan_kaifangpingtai_mofaxiao_ms/tracelog.git"
            }
       ],
   ```
       
   "require" 字段增加 
   ```php
   "cactus/tracelog": "v1.0.1"
   ```
   ```php
   composer update cactus/tracelog 
   ```
   
2. 注册provider
    ```php
    $app->register(\Cactus\TraceLog\TraceLogServiceProvider::class);
    ```
3. 引入middleware
    在bootstrap/app.php 增加
    ```php
    $app->middleware([
         \Cactus\TraceLog\TraceLogMiddleWare::class
    ]);
    ```

4. 日志路径
   日志默认存储位置为 storage/logs 下
   json 目录为json格式输出
   
5. 微服务中 guzzle 默认 Client替换为 \Cactus\Trace\HttpClient 实例
    ```php
    $client = new HttpClient();
    ```
    或者直接使用 app('rpcClient')   此为GuzzleClient扩展客户端，接口方法保持一直

6. 输出示例
```json
{
	"message": "apilog",
	"context": {
		"apitrace": {
			"Host": "cactusserver-mall.test",
			"Method": "GET",
			"Headers": "{\"connection\":[\"keep-alive\"],\"accept\":[\"*/*\"],\"accept-encoding\":[\"gzip, deflate\"],\"user-agent\":[\"python-requests/2.21.0\"],\"host\":[\"cactusserver-mall.test\"],\"content-length\":[\"\"],\"content-type\":[\"\"]}",
			"PathInfo": "/version",
			"QueryString": null,
			"Protocol": "HTTP/1.1",
			"IP": "127.0.0.1",
			"User-Agent": "python-requests/2.21.0",
			"Params": "[]",
			"RequestUri": "/version",
			"RequestTime": "2019-04-11 14:20:36",
			"Response": "{\"data\":{\"version\":\"Lumen (5.5.2) (Laravel Components 5.5.*)\"},\"meta\":{\"timestamp\":1554963636.89317,\"response_time\":0.0965871810913086}}",
			"Status": 200,
			"ResponseTime": "2019-04-11 14:20:36",
			"ElapseTime": "9ms"
		}
	},
	"level": 200,
	"level_name": "INFO",
	"channel": "lumen",
	"datetime": {
		"date": "2019-04-11 14:20:36.917776",
		"timezone_type": 3,
		"timezone": "Asia/Shanghai"
	},
	"extra": {
		"uid": "c36ea4f2386368c1bff60ed0"
	},
	"Trace": {
		"TraceId": "trace:15549636365caedcb4de348",
		"TraceIndex": "0.1",
		"TracePath": "/Mall"
	}
}
```
Trace信息会通过header进行传递