<?php


namespace EasySwoole\Mqtt\Protocol;


use EasySwoole\Mqtt\Protocol\Event\DefaultEvent\ConnectToServerEvent;
use EasySwoole\Mqtt\Protocol\Event\MessageServerEvent;
use EasySwoole\Mqtt\Protocol\Message;
use EasySwoole\Mqtt\Protocol\Reply\ConAck;

class MqttServerEvent
{
    private static $instance;

    private $server;

    private $on = [
        'connectToServer' => ConnectToServerEvent::class,
        'confirmConnectionRequest' => '',
        'partyMessage' => '',
        'releaseConfirmation' => '',
        'releaseReceiv' => '',
        'releaseRelease' => '',
        'releaseToComplete' => '',
        'subscribeToTopic' => '',
        'subscriptionConfirmation' => '',
        'cancelSubscription' => '',
        'cancelSubscriptionConfirmation' => '',
        'heartRateRequest' => '',
        'heartRateResponse' => '',
        'disconnect' => '',
    ];

    /**
     * 获取单例, 服务端只能存在一个
     * @param $port
     * @return MqttServerEvent
     */
    static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * MqttServerEvent constructor.
     * @param mixed $port
     */
    public function init($server)
    {
        if($this->server !== null) {
            return $this;
        }
        $this->server = $server;
        $this->initSetConf();
        $this->initSetOn();

        return $this;
    }

    /**
     * 设置服务配置
     */
    private function initSetConf()
    {
        $this->getServer()->set(
            [
                'open_mqtt_protocol' => true,
                'dispatch_mode' => 2,
            ]
        );
    }

    public function getServer()
    {
        return $this->server;
    }

    private function initSetOn()
    {
        $this->getServer()->on('Connect', function (...$arg) {
            $this->onConnect(...$arg);
        });
        $this->getServer()->on('close', function (...$arg) {
            $this->onClose(...$arg);
        });
        $this->getServer()->on('Receive', function (...$arg) {
            $this->onReceive(...$arg);
        });
    }

    private function onConnect($server, $fd, $reactor_id)
    {
        echo "fd:{$fd} 已连接\n";
    }

    /**
     * mqtt 客户端信息接收回调
     * todo 通讯时协议遵循检测 目前支持 mqtt 第三版
     * @param $server
     * @param int $fd
     * @param int $from_id
     * @param $data
     */
    private function onReceive($server, int $fd, int $from_id, $data ) {
        $message = new Message($data);
        $command = $message->getCommand();

        switch ($command) {
            case $message::CONNECT:
                {
                    $messageServerEvent = MessageServerEvent::getInstance();
                    $eventObj = $this->getOn('connectToServer');
                    //连接标志检测

                    $reply = $messageServerEvent->connectToServer($message, $eventObj);

                    $server->send($reply);
                    break;
                }
            case $message::PUBLISH:
                {
                    $this->decodePublish();
                    break;
                }

            case $message::PUBACK:
                {
                    $this->decodePubAck();
                    break;
                }

            case $message::PUBREC:
                {
                    $this->decodePubRec();
                    break;
                }

            case $message::PUBREL:
                {
                    $this->decodePubRel();
                    break;
                }

            case $message::PUBCOMP:
                {
                    $this->decodePubComp();
                    break;
                }

            case $message::SUBSCRIBE:
                {
                    $this->decodeSubscribe();
                    break;
                }

            case $message::UNSUBSCRIBE:
                {
                    $this->decodeUnSubscribe();
                    break;
                }

            case $message::PINGREQ:
                {
                    break;
                }

            case $message::DISCONNECT:
                {
                    //not action
                    break;
                }

            default:
                {

                }
        }
    }


    private function onClose($serv, $fd, $from_id) {
        $this->debug("Client {$fd} close connection");
//        $client_id = $this->redis_get("client_".$fd);
//        $this->redis_delete("client_".$fd);
//        $this->redis_delete("fd_".$client_id);
        $this->debug("delete client redis data");
    }


    public function debug($str,$title = "Debug")
    {
        echo "-------------------------------\n";
        echo '[' . time() . "] ".$title .':['. $str . "]\n";
        echo "-------------------------------\n";
    }
    public function printstr($string){
        $strlen = strlen($string);
        for($j=0;$j<$strlen;$j++){
            $num = ord($string{$j});
            if($num > 31)
                $chr = $string{$j}; else $chr = " ";
            printf("%4d: %08b : 0x%02x : %s \n",$j,$num,$num,$chr);
        }
    }

    /**
     * 注册 mqtt 事件
     *
     * @param $key string
     * @param $class  string
     * @return bool
     */
    public function on(string $key,string $class)
    {
        if(!array_key_exists($key, $this->on)) {
            echo PHP_EOL . 'on 事件 key 不存在' . PHP_EOL;
            return false;
        }
        if(!class_exists($class)) {
            echo PHP_EOL . 'on 事件注册时 class 不存在' . PHP_EOL;
            return false;
        }
        $this->on[$key] = $class;

    }

    /**
     * 获取用户自定义事件处理
     *
     * @param $key
     * @return mixed
     */
    private function getOn($key)
    {
        $objClass = $this->on[$key];

        return $objClass::getInstance();
    }

}