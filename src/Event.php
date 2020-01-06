<?php
namespace EasySwoole\MQTT;

use EasySwoole\MQTT\Protocol\Message;
use EasySwoole\MQTT\Protocol\Reply\ConAck;
use EasySwoole\MQTT\Protocol\Reply\PingResp;
use EasySwoole\MQTT\Protocol\Reply\PubAck;
use EasySwoole\MQTT\Protocol\Reply\SubAck;
use EasySwoole\MQTT\Protocol\Reply\UnSubAck;

class Event implements EventInterface
{

    function onConnect(Message $message,int $fd): ConAck
    {
        // TODO: Implement onConnect() method.
        var_dump('connection');
        return new ConAck($message);
    }

    function onPublish(Message $message, int $fd): PubAck
    {
        // TODO: Implement onPublish() method.
        var_dump('onPublish');
        return new PubAck($message);
    }

    public function onSubscribe(Message $message, int $fd): SubAck
    {
        // TODO: Implement onSubscribe() method.
        var_dump('onSubscribe');
        return new SubAck($message);
    }

    public function onUnsubscribe(Message $message, int $fd): UnSubAck
    {
        // TODO: Implement onUnsubscribe() method.
        var_dump('onUnsubscribe');
        return new UnSubAck($message);
    }

    public function onPingreq(Message $message, int $fd): PingResp
    {
        // TODO: Implement onPing() method.
        var_dump('onPing');
        return new PingResp($message);
    }

    public function onDisconnect(Message $message, int $fd)
    {
        // TODO: Implement onDisconnect() method.
        var_dump('onDisconnect');
    }
}
