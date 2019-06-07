<?php


namespace EasySwoole\Mqtt\Protocol\Reply;


class PubRec extends Reply
{
    function __toString()
    {
        return chr(0x62) . chr(0x02) . $this->parser->getPacketId();
    }
}