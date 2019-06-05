<?php


namespace EasySwoole\Mqtt\Protocol\Reply;


class PubAck
{
    /** Qos 1的时候使用 */
    private $packetId;
    function __construct($packetId)
    {
        $this->packetId = $packetId;
    }

    function __toString()
    {
        return chr(0x40) . chr(0x02) . $this->packetId;
    }
}