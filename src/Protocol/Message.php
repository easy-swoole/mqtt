<?php


namespace EasySwoole\Mqtt\Protocol;

use EasySwoole\Mqtt\Protocol\Event\DefaultEvent\ConnectToServerEvent;
use EasySwoole\Mqtt\Protocol\Event\MessageServerEvent;
use EasySwoole\Mqtt\Protocol\Face\ParseInterface;
use EasySwoole\Mqtt\Protocol\Inherit\Singleton;
use EasySwoole\Mqtt\Protocol\Reply\ConAck;

/**
 * mqtt消息对象
 * @package EasySwoole\Mqtt\Protocol
 */
class Message
{
    /*
     * 名字 	值 	流向 	描述
        CONNECT 	1 	C->S 	客户端请求与服务端建立连接
        CONNACK 	2 	S->C 	服务端确认连接建立
        PUBLISH 	3 	CóS 	发布消息
        PUBACK 	4 	CóS 	收到发布消息确认
        PUBREC 	5 	CóS 	发布消息收到
        PUBREL 	6 	CóS 	发布消息释放
        PUBCOMP 	7 	CóS 	发布消息完成
        SUBSCRIBE 	8 	C->S 	订阅请求
        SUBACK 	9 	S->C 	订阅确认
        UNSUBSCRIBE 	10 	C->S 	取消订阅
        UNSUBACK 	11 	S->C 	取消订阅确认
        PING 	12 	C->S 	客户端发送PING(连接保活)命令
        PINGRSP 	13 	S->C 	PING命令回复
        DISCONNECT 	14 	C->S 	断开连接
     */
    const CONNECT = 1;
    const CONNACK = 2;
    const PUBLISH = 3;
    const PUBACK = 4;
    const PUBREC = 5;
    const PUBREL = 6;
    const PUBCOMP = 7;
    const SUBSCRIBE = 8;
    const SUBACK = 9;
    const UNSUBSCRIBE = 10;
    const UNSUBACK = 11;
    const PINGREQ = 12;
    const PINGRESP = 13;
    const DISCONNECT = 14;

    /**
     * MQTT报文类型
     * @var integer 0-15
     */
    protected $type = 0;

    /**
     * 消息byte长度
     *
     * @var int
     */
    protected $length = 0;

    /**
     * @var BufferParser
     */
    private $parse;

    public function __construct($data)
    {
        $parse = $this->decodeMqtt($data);
        $this->parse = $parse;
    }

    public function __call($name, $args) {
        return $this->parse->$name(...$args);
    }


    public function decodeMqtt($data)
    {
        return new BufferParser($data);

    }


}