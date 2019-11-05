<?php


namespace EasySwoole\Mqtt\Protocol;


use EasySwoole\Mqtt\Protocol\CacheDrive\RedisDrive;
use EasySwoole\Mqtt\Protocol\Controller\Message;
use EasySwoole\Mqtt\Protocol\Event\DefaultEvent\ConnectToServerEvent;
use EasySwoole\Mqtt\Protocol\Event\MessageServerEvent;
use EasySwoole\Mqtt\Protocol\Face\Cache;
use EasySwoole\Mqtt\Protocol\Inherit\Singleton;

class MqttServerEvent
{
    use Singleton;
    /**
     * swoole server
     * @var
     */
    private $server;

    /**
     * 消息事件
     * @var MessageServerEvent
     */
    protected $messageServerEvent;

    /**
     * 消息类型对应不同的事件
     * @var array
     */
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

    private $cacheDrive = RedisDrive::class;


    private function __construct($server)
    {
        if($this->server !== null) {
            return $this;
        }
        $this->server = $server;
        $this->messageServerEvent = MessageServerEvent::getInstance();
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
            ]
        );
    }

    public function getServer()
    {
        return $this->server;
    }

    /**
     * 初始化事件
     * todo 事件同用户自定义事件同时初始化
     */
    private function initSetOn()
    {
        $this->getServer()->on('Connect', function (\swoole_server $server, int $fd, int $reactorId) {
            $this->onConnect($server,  $fd, $reactorId);
        });
        $this->getServer()->on('close', function (\swoole_server $server, int $fd, int $reactorId) {
            $this->onClose($server,  $fd, $reactorId);
        });
        $this->getServer()->on('Receive', function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
            $this->onReceive($server, $fd, $reactor_id, $data);
        });
    }

    private function onConnect($server,  $fd, $reactorId)
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
    private function onReceive(\swoole_server $server, int $fd, int $reactor_id, string $data) {
        $message = new Message($data);

        switch ($message->getCommand()) {
            case $message::CONNECT:
                {
                    $message->decodeConnect();
                    $messageServerEvent = $this->getMessageServerEvent();
                    $reply = $messageServerEvent->connectToServer($message, $this->getOn('connectToServer'), $fd);
                    $server->send($fd, $reply);

                    break;
                }
            case $message::PUBLISH:
                {
                    $message->decodePublish();
                    break;
                }

            case $message::PUBACK:
                {
                    $message->decodePubAck();
                    break;
                }

            case $message::PUBREC:
                {
                    $message->decodePubRec();
                    break;
                }

            case $message::PUBREL:
                {
                    $message->decodePubRel();
                    break;
                }

            case $message::PUBCOMP:
                {
                    $message->decodePubComp();
                    break;
                }

            case $message::SUBSCRIBE:
                {
                    $message->decodeSubscribe();
                    break;
                }

            case $message::UNSUBSCRIBE:
                {
                    $message->decodeUnSubscribe();
                    break;
                }

            case $message::PINGREQ:
                {
                    $message->decodePingReq();
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


    private function onClose($server,  $fd, $reactorId) {
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
     * todo 数组注册
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

    /**
     * @return MessageServerEvent
     */
    public function getMessageServerEvent()
    {
        return $this->messageServerEvent;
    }

    /**
     * 设置默认的缓存驱动
     * @param string $cacheDrive
     * @return bool
     */
    public function setCacheDrive(string $cacheDrive)
    {
        if(!class_exists($cacheDrive)) {
            return false;
        }
        if(!( $cacheDrive instanceof Cache)) {
            return false;
        }
        $this->cacheDrive = $cacheDrive;
        return true;
    }

}