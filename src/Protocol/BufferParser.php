<?php


namespace EasySwoole\Mqtt\Protocol;


class BufferParser
{
    //值1 	C->S 	客户端请求与服务端建立连接
    const CONNECT = 1;
    //值2 	S->C 	服务端确认连接建立
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

    private $command;

    private $qos;
    /**
     * @var string topic
     */
    private $topic;
    /**
     * @var int retain
     */
    private $retain;
    /**
     * @var int requested QoS in subscribe
     */
    private $requestQos;
    /**
     * @var string message body
     */
    private $payload;
    /**
     * @var int packet identifier
     */
    private $packetId;
    /**
     * @var int remaining length
     */
    private $remainLen;
    /**
     * @var array connect info
     */
    private $connectInfo = [];
    /**
     * @var string buffer
     */
    private $buffer;


    function __construct(string $buffer)
    {
        $this->buffer = $buffer;
        $this->decodeFixedHeader();
        switch ($this->command){
            case self::CONNECT:{
                $this->decodeConnect();
                break;
            }
            case self::PUBLISH:{
                $this->decodePublish();
                break;
            }
            case self::DISCONNECT:{
                //not action
                break;
            }
            default:{
                var_dump($this->command);
            }
        }
    }

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

    /*
     * 解析固定头
     */
    private function decodeFixedHeader()
    {
        $byte = $this->bufferPop(0);
        $byte = ord($byte);
        $this->command = ($byte & 0xF0) >> 4;
        $this->qos = ($byte & 0x06) >> 1;
        $this->retain = $byte & 0x01;
        $this->remainLen = $this->getRemainBufferLen();
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
    public function getRequestQos(): int
    {
        return $this->requestQos;
    }

    /**
     * @param int $requestQos
     */
    public function setRequestQos(int $requestQos): void
    {
        $this->requestQos = $requestQos;
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
     * @return array
     */
    public function getConnectInfo(): array
    {
        return $this->connectInfo;
    }

    /**
     * @param array $connectInfo
     */
    public function setConnectInfo(array $connectInfo): void
    {
        $this->connectInfo = $connectInfo;
    }
}