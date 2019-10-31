<?php
require '../../vendor/autoload.php';
class Server
{
    private $serv;
    private $conf;
    private $event;
    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'open_mqtt_protocol' => true,
            'dispatch_mode' => 2,
            'debug_mode'=> 1
        ));
//        $this->event = new \EasySwoole\Mqtt\Protocol\Reply\ServerEvent();
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->start();

    }
    public function onStart( $serv ) {
//        $this->debug("onStart");
    }

    /**
     * Worker进程创建事件
     * @param $serv
     * @param $worker_id
     */
    public function onWorkerStart($serv,$worker_id){
//        $this->debug('onWorkerStart');
//        if ($worker_id == 1)
//        {
//            $serv->tick(1000, function () {
//                echo 'test';
//            });
//        }
    }


    public function onConnect( $serv, $fd, $from_id ) {
//        $this->debug('onConnect');
        $this->event->connectToServer(func_get_args());

//        $serv->send( $fd, "Hello {$fd}!" );
//        swoole_timer_tick(1000, function(){
//            echo "timeout\n";
//        });
    }

    /**
     * 接受到数据
     * @param swoole_server $serv
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
        //接受到数据包. 使用 mqtt 解析方式解析
//        $this->esMqtt($serv, $fd, $from_id, $data);
        $this->decode_mqtt($data,$serv,$fd);
    }

    public function esMqtt($server, $fd, $from_id, $data)
    {
        $parser = new \EasySwoole\Mqtt\Protocol\BufferParser($data);
        $reply = null;

        switch ($parser->getCommand())
        {
            case $parser::CONNECT:
                $conAck = new \EasySwoole\Mqtt\Protocol\Reply\ConAck();
                if($parser->getConnectInfo('cleanSession')) {
                    $conAck->cleanSession();
                }
                $reply = (string)$conAck;
                break;
            case $parser::PUBLISH:
//        $parser::printStr($data);
                if($parser->getQos() == 1){
                    $reply = new \EasySwoole\Mqtt\Protocol\Reply\PubAck($parser);
                }else if($parser->getQos() == 2){
                    $reply = new \EasySwoole\Mqtt\Protocol\Reply\PubRec($parser);
                }
                break;
            default:
                var_dump($parser->toArray());
        }
        if($reply){
            $server->send($fd,$reply);
        }
    }
    public function onClose( $serv, $fd, $from_id ) {
        $this->debug("Client {$fd} close connection");
    }

    public function decode_mqtt($data,$serv,$fd)
    {
        $this->printstr($data);

        $data_len_byte = 1;
        $fix_header['data_len'] = $this->getmsglength($data,$data_len_byte);

//        $this->debug($fix_header['data_len'],"get msg length");
        //$data 十六进制
        //   0: 00010000 : 0x10 :
        //   1: 00010010 : 0x12 :
        //   2: 00000000 : 0x00 :
        //   3: 00000100 : 0x04 :
        //   4: 01001101 : 0x4d : M
        //   5: 01010001 : 0x51 : Q
        //   6: 01010100 : 0x54 : T
        //   7: 01010100 : 0x54 : T
        //   8: 00000100 : 0x04 :
        //   9: 00000010 : 0x02 :
        //  10: 00000000 : 0x00 :
        //  11: 00111100 : 0x3c : <
        //  12: 00000000 : 0x00 :
        //  13: 00000110 : 0x06 :
        //  14: 01110001 : 0x71 : q
        //  15: 01101001 : 0x69 : i
        //  16: 01100001 : 0x61 : a
        //  17: 01101111 : 0x6f : o
        //  18: 01100001 : 0x61 : a
        //  19: 01101110 : 0x6e : n
        $byte = ord($data[0]);//解析 string 二进制值第一个字节(8bit)为 0 到 255 范围的无符号整型类型。
//        $this->printstr(($byte & 0xF0));
//        $this->printstr((($byte & 0xF0) >> 4));
        $fix_header['type'] = ($byte & 0xF0) >> 4; //1 0xF0作用是.排除前4位之外其他位为0
        var_dump($byte >> 4);
//        $this->printstr($fix_header['type']);
        switch ($fix_header['type'])
        {
            case 1:
                $this->debug("CONNECT");
                $resp = chr(32) . chr(2) . chr(0) . chr(0);//转换为二进制返回应该使用chr
                $client_info = $this->get_connect_info(substr($data, 2));
                $client_id = $client_info['clientId'];
                $serv->send($fd, $resp);
                $this->debug("Send CONNACK");
                break;
            case 3:
                $this->debug("PUBLISH");

                $fix_header['dup'] = ($byte & 0x08) >> 3;
                $fix_header['qos'] = ($byte & 0x06) >> 1;
                $fix_header['retain'] = $byte & 0x01;

                $offset = 2;
                $topic = $this->decodeString(substr($data, $offset));
                $offset += strlen($topic) + 2;
                $msg = substr($data, $offset);
                echo "client msg: $topic\n---------------------------------\n$msg\n---------------------------------\n";
//                $client_id = $this->redis_get("client_".$fd);
                break;
            case 8:
                $this->debug("SUBSCRIBE");
                //id有可能是两个字节的,这个需要更多的测试
//                $msg_id = ord($data[2]);
                $msg_id = ord($data[3]);
                $fix_header['sign'] = ($byte & 0x02) >> 1;
                $qos = ord($data[$fix_header['data_len']+1]);
                if($fix_header['sign']==1)
                {
                    echo "this is subscribe message!!!!\n";
                    $this->debug($msg_id,"msg id");
                    $this->debug($qos,"QOS");
                    //这里没有从协议中读取topic的长度,按照固定的写做6
                    $topic = substr($data,6,$fix_header['data_len']-1);
                    $this->debug($topic,"topic");
                }
                //订阅后返回
                $resp = chr(0x90).chr(3).chr(0).chr($msg_id).chr(0);
                $this->printstr($resp);
                $serv->send($fd,$resp);
                $this->debug("send SUBACK");
                break;
            case 10:
                $this->debug("UNSUBSCRIBE");
                break;
            case 12:
                $this->debug("PINGREQ");
                $resp = chr(208) . chr(0);//转换为二进制返回应该使用chr
                //保存最后ping的时间
                $serv->send($fd, $resp);
                $this->debug("Send PINGRESP");
                break;
            case 14:
                $this->debug("DISCONNECT");
                break;
        }
    }
    public function decodeValue($data)
    {
        return 256 * ord($data[0]) + ord($data[1]);
    }
    public function decodeString($data)
    {
        $length = $this->decodeValue($data);
        return substr($data, 2, $length);
    }
    public function get_connect_info($data)
    {
        $connect_info['protocol_name'] = $this->decodeString($data);
        $offset = strlen($connect_info['protocol_name']) + 2;
        $connect_info['version'] = ord(substr($data, $offset, 1));
        $offset += 1;
        $byte = ord($data[$offset]);
        $connect_info['willRetain'] = ($byte & 0x20 == 0x20);
        $connect_info['willQos'] = ($byte & 0x18 >> 3);
        $connect_info['willFlag'] = ($byte & 0x04 == 0x04);
        $connect_info['cleanStart'] = ($byte & 0x02 == 0x02);
        $offset += 1;
        $connect_info['keepalive'] = $this->decodeValue(substr($data, $offset, 2));
        $offset += 2;
        $connect_info['clientId'] = $this->decodeString(substr($data, $offset));
        return $connect_info;
    }
    public function debug($str,$title = "Debug")
    {
        echo "-------------------------------\n";
        echo '[' . time() . "] ".$title .':['. $str . "]\n";
        echo "-------------------------------\n";
    }
    public function printstr($string){
        echo "\n";
        $strlen = strlen($string);
        for($j=0;$j<$strlen;$j++){
            $num = ord($string{$j});
            if($num > 31)
                $chr = $string{$j}; else $chr = " ";
            printf("%4d: %08b : 0x%02x : %s \n",$j,$num,$num,$chr);
        }
        echo "\n";
    }
    /* getmsglength: */
    public function getmsglength(&$msg, &$i){
        $multiplier = 1;
        $value = 0 ;
        do{
            $digit = ord($msg{$i});
            $value += ($digit & 127) * $multiplier;
            $multiplier *= 128;
            $i++;
        }while (($digit & 128) != 0);
        return $value;
    }
    /* setmsglength: */
    public function setmsglength($len){
        $string = "";
        do{
            $digit = $len % 128;
            $len = $len >> 7;
            // if there are more digits to encode, set the top bit of this digit
            if ( $len > 0 )
                $digit = ($digit | 0x80);
            $string .= chr($digit);
        }while ( $len > 0 );
        return $string;
    }
    /* strwritestring: writes a string to a buffer */
    public function strwritestring($str, &$i){
        $ret = " ";
        $len = strlen($str);
        $msb = $len >> 8;
        $lsb = $len % 256;
        $ret = chr($msb);
        $ret .= chr($lsb);
        $ret .= $str;
        $i += ($len+2);
        return $ret;
    }
    /* publish: publishes $content on a $topic */
    function send_publish($topic, $content, $qos = 0, $retain = 0)
    {
        $buffer = "";
        $buffer .= $topic;
        $buffer .= $content;
        $head = " ";
        $cmd = 0x30;
        $head{0} = chr($cmd);
        $head .= $this->setmsglength(strlen($topic)+strlen($content)+2);

        echo "+++++++++++++++++++++++++++\n";
        $this->printstr($head.chr(0).chr(0x06).$buffer);
        echo "+++++++++++++++++++++++++++\n";
        return $head.chr(0).chr(0x06).$buffer;
    }
}

$server = new Server();


