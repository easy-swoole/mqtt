<?php


namespace EasySwoole\Mqtt\Protocol;


use EasySwoole\Mqtt\Protocol\Face\ParseInterface;
use EasySwoole\Spl\SplBean;

class BufferParser extends SplBean implements ParseInterface
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

    protected $command;

    protected $qos;
    /**
     * @var string topic
     */
    protected $topic;
    /**
     * @var int retain
     */
    protected $retain;
    /**
     * @var int requested QoS in subscribe
     */
    protected $requestSubscribeQos;
    /**
     * @var string message body
     */
    protected $payload;
    /**
     * @var int packet identifier
     */
    protected $packetId;
    /**
     * @var int remaining length
     */
    protected $remainLen;
    /**
     * @var array connect info
     */
    protected $connectInfo = [];
    /*
     * dup flag
     */
    protected $dup;

    /**
     * @var string buffer
     */
    private $buffer;

    private $error;


    function __construct(string $buffer)
    {
        parent::__construct([]);
        $this->buffer = $buffer;
        $this->decodeFixedHeader();
//        switch ($this->command){
//            case self::CONNECT:{
//                $this->decodeConnect();
//                break;
//            }
//            case self::PUBLISH:{
//                $this->decodePublish();
//                break;
//            }
//
//            case self::PUBACK:{
//                $this->decodePubAck();
//                break;
//            }
//
//            case self::PUBREC:{
//                $this->decodePubRec();
//                break;
//            }
//
//            case self::PUBREL:{
//                $this->decodePubRel();
//                break;
//            }
//
//            case self::PUBCOMP:{
//                $this->decodePubComp();
//                break;
//            }
//
//            case self::SUBSCRIBE:{
//                $this->decodeSubscribe();
//                break;
//            }
//
//            case self::UNSUBSCRIBE:{
//                $this->decodeUnSubscribe();
//                break;
//            }
//
//            case self::PINGREQ:{
//                break;
//            }
//
//            case self::DISCONNECT:{
//                //not action
//                break;
//            }
//
//            default:{
//
//            }
//        }
    }

    /*
     * 解析固定头
     */
    private function decodeFixedHeader()
    {
        $byte = $this->bufferPop(0);
        $byte = ord($byte);
        $this->command = ($byte & 0xF0) >> 4;
        $this->dup = ($byte & 0x08) >> 3;
        $this->qos = ($byte & 0x06) >> 1;
        $this->retain = $byte & 0x01;
        $this->remainLen = $this->getRemainBufferLen();
    }

    /*
     * 解析连接请求
     */
    private function decodeConnect()
    {
        $info = [];
        $info['protocol'] = $this->bufferPop();
        $info['version'] = ord($this->bufferPop(0));
        $byte = ord($this->bufferPop(0));
        $info['auth'] = ($byte & 0x80) >> 7;
        $info['auth'] &= ($byte & 0x40) >> 6;
        $info['willRetain'] = ($byte & 0x20) >> 5;
        $info['willQos'] = ($byte & 0x18) >> 3;
        $info['willFlag'] = ($byte & 0x04);
        $info['cleanSession'] = ($byte & 0x02) >> 1;
        $keep_alive = $this->bufferPop(0, 2);
        $info['keepAlive'] = 256 * ord($keep_alive[0]) + ord($keep_alive[1]);
        $info['clientId'] = $this->bufferPop();
        if ($info['auth']) {
            $info['username'] = $this->bufferPop();
            $info['password'] = $this->bufferPop();
        }
        $this->connectInfo = $info;
    }

    private function decodePublish()
    {
        $this->topic = $this->bufferPop();
        //仅单Qos不为0 的时候，需要PubAck或PubRec回复
        if ($this->qos > 0) {
            $this->packetId = $this->bufferPop(0, 2);
        }
        $this->payload = $this->buffer;
    }

    private function decodePubAck()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    private function decodePubRec()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    private function decodePubRel()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    private function decodePubComp()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    private function decodeSubscribe()
    {
        $this->packetId = $this->bufferPop(0, 2);
        $this->topic = $this->bufferPop();
        $this->requestSubscribeQos = ord($this->bufferPop());
    }

    private function decodeUnSubscribe()
    {
        $this->packetId = $this->bufferPop(0, 2);
        $this->topic = $this->bufferPop();
    }

    private function decodePingReq()
    {

    }

    private function bufferPop($flag = 1, $len = 1):string
    {
        if ($flag === 1) {
            $len = 256 * ord($this->bufferPop(0)) + ord($this->bufferPop(0));
        }
        if (strlen($this->buffer) < $len) {
            return '';
        }
        preg_match('/^([\x{00}-\x{ff}]{' . $len . '})([\x{00}-\x{ff}]*)$/s', $this->buffer, $matches);
        $this->buffer = $matches[2];
        return $matches[1];
    }



    private function getRemainBufferLen():int
    {
        $multiplier = 1;
        $value = 0;
        do {
            $encodedByte = ord($this->bufferPop(0));
            $value += ($encodedByte & 127) * $multiplier;
            if ($multiplier > 128 * 128 * 128) $value = -1;
            $multiplier *= 128;
        } while (($encodedByte & 128) != 0);
        return $value;
    }

    public function getError() : bool
    {
        return $this->error;
    }
    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getQos()
    {
        return $this->qos;
    }

    /**
     * @param mixed $qos
     */
    public function setQos($qos): void
    {
        $this->qos = $qos;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * @return int
     */
    public function getRetain(): int
    {
        return $this->retain;
    }

    /**
     * @param int $retain
     */
    public function setRetain(int $retain): void
    {
        $this->retain = $retain;
    }

    /**
     * @return int
     */
    public function getRequestSubscribeQos(): int
    {
        return $this->requestSubscribeQos;
    }

    /**
     * @param int $requestSubscribeQos
     */
    public function setRequestSubscribeQos(int $requestSubscribeQos): void
    {
        $this->requestSubscribeQos = $requestSubscribeQos;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     */
    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @return int
     */
    public function getPacketId()
    {
        return $this->packetId;
    }

    /**
     * @param int $packetId
     */
    public function setPacketId(int $packetId): void
    {
        $this->packetId = $packetId;
    }

    /**
     * 获取连接会话信息
     * @param null $key
     * @return array|mixed|null
     */
    public function getConnectInfo($key = null)
    {
        if($key) {
            return $this->connectInfo[$key] ?? null;
        }
        return $this->connectInfo;


    }

    /**
     * @param array $connectInfo
     */
    public function setConnectInfo(array $connectInfo): void
    {
        $this->connectInfo = $connectInfo;
    }

    public static function byteToBit(string $byte)
    {
        $bin = decbin(ord($byte));
        $bin = str_pad($bin, 8, 0, STR_PAD_LEFT);
        $ret = [];
        $i = 7;
        while ($i >= 0){
            $ret[7-$i] = (int)$bin[$i];
            $i--;
        }
        return $ret;
    }

    public static function printStr($string)
    {
        $strlen = strlen($string);
        for ($j = 0; $j < $strlen; $j++) {
            $num = ord($string{$j});
            if ($num > 31)
                $chr = $string{$j};
            else
                $chr = " ";
            printf("%4d: %08b : 0x%02x : %s \n", $j, $num, $num, $chr);
        }
    }
}