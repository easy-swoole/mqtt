<?php


namespace EasySwoole\MQTT\Protocol\Reply;


class SubAck extends Reply
{
    function __toString()
    {
        $payload = chr($this->parser->getRequestSubscribeQos());
        return chr(0x90) . ($payload === '' ? chr(0x02) : chr(0x02 + strlen($payload))) . $this->parser->getPacketId() . $payload;
    }
}