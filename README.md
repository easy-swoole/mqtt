## 开启服务端
参考:https://www.easyswoole.com/Socket/tcp.html
创建 tcp 服务
并设置 swoole 中 `open_mqtt_protocol` 配置为 `true`

###  Easyswoole 已经开启`EASYSWOOLE_SERVER` 直接配置回调
```php
public static function mainServerCreate(EventRegister $register)
{
    $register->add($register::onReceive, function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
        echo "fd:{$fd} 发送消息:{$data}\n";
    });
}

```
### 单独端口开启

单独端口开启TCP服务器，需要添加子服务。

通过EasySwooleEvent.php文件的mainServerCreate 事件,进行子服务监听,例如:


```php
public static function mainServerCreate(EventRegister $register)
{
    $server = ServerManager::getInstance()->getSwooleServer();

    $subPort1 = $server->addlistener('0.0.0.0', 9502, SWOOLE_TCP);
    $subPort1->set(
        [
            'open_length_check' => false, //不验证数据包
        ]
    );
    $subPort1->on('connect', function (\swoole_server $server, int $fd, int $reactor_id) {
        echo "fd:{$fd} 已连接\n";
        $str = '恭喜你连接成功';
        $server->send($fd, $str);
    });
    $subPort1->on('close', function (\swoole_server $server, int $fd, int $reactor_id) {
        echo "fd:{$fd} 已关闭\n";
    });
    $subPort1->on('receive', function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
        echo "fd:{$fd} 发送消息:{$data}\n";
    });
}

```

## 测试服务端

```

$server = new swoole_server("127.0.0.1", 9600);

$server->set([
    'open_mqtt_protocol'=>true
]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {

    $parser = new \EasySwoole\Mqtt\Protocol\BufferParser($data);
    $reply = null;
    if($parser->getCommand() == $parser::CONNECT){
        $reply = (string)(new \EasySwoole\Mqtt\Protocol\Reply\ConAck());
    }else if($parser->getCommand() == $parser::PUBLISH){
//        $parser::printStr($data);
        if($parser->getQos() == 1){
            $reply = new \EasySwoole\Mqtt\Protocol\Reply\PubAck($parser);
        }else if($parser->getQos() == 2){
            $reply = new \EasySwoole\Mqtt\Protocol\Reply\PubRec($parser);
        }
    }else{
        var_dump($parser->toArray());
    }

    if($reply){
        $server->send($fd,$reply);
    }

});
$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});
$server->start();
```
## 参考文献
- https://mcxiaoke.gitbooks.io/mqtt-cn/content/mqtt/01-Introduction.html