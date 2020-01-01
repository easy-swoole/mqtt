<?php


namespace EasySwoole\MQTT;


use EasySwoole\MQTT\Protocol\Message;
use EasySwoole\MQTT\Protocol\Reply\ConAck;

class Event implements EventInterface
{
    function onConnect(Message $message,int $fd): ConAck
    {
        // TODO: Implement onConnect() method.
    }
}
