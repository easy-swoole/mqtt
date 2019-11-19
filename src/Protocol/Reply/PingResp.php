<?php


namespace EasySwoole\MQTT\Protocol\Reply;


class PingResp extends Reply
{
    function __toString()
    {
        return chr(0xD0) . chr(0);
    }
}