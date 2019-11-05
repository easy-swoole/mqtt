<?php


namespace EasySwoole\Mqtt\Protocol\Controller;


use EasySwoole\Mqtt\Protocol\Face\ParseInterface;
use EasySwoole\Spl\SplBean;

class BufferParser extends SplBean implements ParseInterface
{

    protected $byteHex;
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

    private $errors = [];


    function __construct(string $buffer)
    {
        parent::__construct([]);
        $this->buffer = $buffer;
        $byteHex  = bin2hex($buffer);
        $this->byteHex = $byteHex;
        $this->decodeFixedHeader();
    }

    /*
     * 解析所有消息类型的固定头数据
     *
     */
    private function decodeFixedHeader()
    {
        $byte = $this->bufferPop(0);
        //ASCII  根据字符编码可能为多个 byte 只获取第一个为固定头
        $byte = ord($byte);
        $this->command = ($byte & 0xF0) >> 4;
        $this->dup = ($byte & 0x08) >> 3;
        $this->qos = ($byte & 0x06) >> 1;
        $this->retain = $byte & 0x01;
        $this->remainLen = $this->getRemainBufferLen();
    }

    public function debug($str,$title = "Debug")
    {
        echo "-------------------------------\n";
        echo '[' . time() . "] ".$title .':['. $str . "]\n";
        echo "-------------------------------\n";
    }

    public function __destruct()
    {
        foreach ($this->errors as $value) {
            echo $value;
        }
    }

    public function errorInsert($data = '', $title = '错误')
    {
        $this->errors[] = '[' . time() . $title . ':' . PHP_EOL . $data . PHP_EOL . ']';
    }


    /*
     * 解析连接服务端请求
     */
    public function decodeConnect()
    {
        $this->printStr($this->buffer);
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
        $info['willTopic'] = '';
        $info['willMessage'] = '';
        //遗嘱
        if($info['willQos']) {
            $info['willTopic'] = $this->bufferPop() ?: $this->errorInsert('遗嘱主题没有设置', 'willTopic');
            $info['willMessage'] = $this->bufferPop() ?: $this->errorInsert('遗嘱信息没有设置', 'willMessage');
        }
        $info['username'] = '';
        $info['password'] = '';
        if ($info['auth']) {
            $info['username'] = $this->bufferPop() ?: $this->errorInsert('用户名没有设置', 'username');
            $info['password'] = $this->bufferPop() ?: $this->errorInsert('密码没有设置', 'password');
        }
        $this->connectInfo = $info;
    }

    public function decodePublish()
    {
        $this->topic = $this->bufferPop();
        //仅单Qos不为0 的时候，需要PubAck或PubRec回复
        if ($this->qos > 0) {
            $this->packetId = $this->bufferPop(0, 2);
        }
        $this->payload = $this->buffer;
    }

    public function decodePubAck()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    public function decodePubRec()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    public function decodePubRel()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    public function decodePubComp()
    {
        $this->packetId = $this->bufferPop(0, 2);
    }

    public function decodeSubscribe()
    {
        $this->packetId = $this->bufferPop(0, 2);
        $this->topic = $this->bufferPop();
        $this->requestSubscribeQos = ord($this->bufferPop());
    }

    public function decodeUnSubscribe()
    {
        $this->packetId = $this->bufferPop(0, 2);
        $this->topic = $this->bufferPop();
    }

    public function decodePingReq()
    {

    }

    /**
     * 获取 buffer 的内容
     * @param int $flag 0为获取接下来两个字节显示的规定长度的内容, 1 为获取全部剩余  buffer
     * @param int $len
     * @return string
     */
    private function bufferPop($flag = 1, $len = 1):string
    {
        $length = $len*2;
        if ($flag === 1) { //获取接下来两个字节显示的规定长度的内容以及获取utf8字符
            $length = (256 * ord($this->bufferPop(0)) + ord($this->bufferPop(0)))*2;
        }
        if (strlen($this->byteHex) < $length) {
            return '';
        }
        $hex = mb_substr($this->byteHex, 0, $length);
        $this->byteHex = mb_substr($this->byteHex, $length);
        return pack('H*', $hex);
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
     * @return array|integer
     */
    public function getConnectInfo($key = null)
    {
        if($key) {
            return $this->connectInfo[$key] ?? 0;
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

    public function printStr($string)
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
        echo PHP_EOL . PHP_EOL;
    }
}