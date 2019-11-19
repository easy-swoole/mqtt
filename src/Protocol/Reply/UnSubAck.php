<?php


namespace EasySwoole\MQTT\Protocol\Reply;


class UnSubAck extends Reply
{
    function __toString()
    {
        return chr(0xB0) . chr(0x02) . $this->parser->getPacketId();
    }
}