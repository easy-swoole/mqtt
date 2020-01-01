## 设计思路
EventInterface 定义了MQTT的全部事件，而Event实现了这个接口，并实现了部分默认功能


## 测试服务端
```
use EasySwoole\MQTT\MQTT;
use EasySwoole\MQTT\Event;
use EasySwoole\MQTT\Protocol\Message;

$server = new swoole_server("127.0.0.1", 9600);

$server->set([
    'open_mqtt_protocol'=>true
]);

$mqtt = new MQTT();
/*
 * 事件注册
 */
$mqtt->event()->set(Event::CONNECT,function (Message $message,int $fd){
    /*
     * 若握手成功返回Reply
     */
});

$mqtt->attachServer($server);

$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});
$server->start();
```
## 参考文献
- https://mcxiaoke.gitbooks.io/mqtt-cn/content/mqtt/01-Introduction.html
- 测试客户端  https://github.com/bluerhinos/phpMQTT/blob/master/phpMQTT.php