<?php
namespace EasySwoole\MQTT;

use EasySwoole\MQTT\Protocol\Message;
use EasySwoole\MQTT\Protocol\Reply\ConAck;
use EasySwoole\MQTT\Protocol\Reply\PingResp;
use EasySwoole\MQTT\Protocol\Reply\PubAck;
use EasySwoole\MQTT\Protocol\Reply\SubAck;
use EasySwoole\MQTT\Protocol\Reply\UnSubAck;

interface EventInterface
{
    public function onConnect(Message $message, int $fd) : ConAck;

    public function onPublish(Message $message, int $fd) : PubAck;

    public function onSubscribe(Message $message, int $fd) : SubAck;

    public function onUnsubscribe(Message $message, int $fd) : UnSubAck;

    public function onPingreq(Message $message, int $fd) : PingResp;

    public function onDisconnect(Message $message, int $fd);
}
