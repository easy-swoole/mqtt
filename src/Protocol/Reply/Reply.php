<?php


namespace EasySwoole\MQTT\Protocol\Reply;


use EasySwoole\Mqtt\Protocol\Message;

class Reply
{
    protected $parser;
    function __construct(?Message $bufferParser = null)
    {
        $this->parser = $bufferParser;
    }
}