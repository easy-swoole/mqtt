<?php


namespace EasySwoole\Mqtt\Protocol\Reply;


use EasySwoole\Mqtt\Protocol\BufferParser;

class Reply
{
    protected $parser;
    function __construct(?BufferParser $bufferParser = null)
    {
        $this->parser = $bufferParser;
    }
}