<?php


namespace EasySwoole\MQTT;


use EasySwoole\MQTT\Protocol\Message;
use EasySwoole\MQTT\Protocol\Reply\ConAck;

interface EventInterface
{
    function onConnect(Message $message,int $fd):ConAck;
}