<?php


namespace EasySwoole\MQTT\Protocol\Reply;


class PubRel extends Reply
{
    function __toString()
    {
        return chr(0x70) . chr(0x02) . $this->parser->getPacketId();
    }
}