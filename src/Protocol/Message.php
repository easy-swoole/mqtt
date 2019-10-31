<?php


namespace EasySwoole\Mqtt\Protocol;

/**
 * mqtt消息对象
 * @package EasySwoole\Mqtt\Protocol
 */
class Message
{
    /**
     * @var integer TCP客户端连接的标识符，在Server实例中是唯一的，在多个进程内不会重复 max = 1600万 正在维持的TCP连接fd不会被复用
     */
    private $fd;
    public function __construct($fd, $data)
    {
        $this->decodeMqtt($data);
    }

    public function decodeMqtt($data)
    {
        $data_len_byte = 1;
        $fix_header['data_len'] = $this->getmsglength($data,$data_len_byte);
        $byte = ord($data[0]);
        $fix_header['type'] = ($byte & 0xF0) >> 4;
    }
}