## 测试服务端
```
require_once 'vendor/autoload.php';


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
            $reply = new \EasySwoole\Mqtt\Protocol\Reply\PubAck($parser->getPacketId());
        }else if($parser->getQos() == 2){
            $reply = new \EasySwoole\Mqtt\Protocol\Reply\PubRec($parser->getPacketId());
        }
        var_dump($parser);
    }else{
        var_dump($parser->getCommand());
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