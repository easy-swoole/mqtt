## 测试服务端
```
use EasySwoole\MQTT\MQTT;

$server = new swoole_server("127.0.0.1", 9600);

$server->set([
    'open_mqtt_protocol'=>true
]);

$mqtt = new MQTT();

$mqtt->attachServer($server);

$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});
$server->start();
```
## 参考文献
- https://mcxiaoke.gitbooks.io/mqtt-cn/content/mqtt/01-Introduction.html
- 测试客户端  https://github.com/bluerhinos/phpMQTT/blob/master/phpMQTT.php