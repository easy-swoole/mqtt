<?php


namespace EasySwoole\Mqtt\Protocol\Reply;


use EasySwoole\Mqtt\Protocol\BufferParser;

class PubAck extends Reply
{

    function __toString()
    {
        if($this->parser->getQos() == 1){
            return chr(0x40) . chr(0x02).$this->parser->getPacketId();
        }else if($this->parser->getQos() == 2){
            return chr(0x50) . chr(0x02).$this->parser->getPacketId();
        }else{
            return '';
        }
    }
}