<?php

namespace EasySwoole\MQTT;

use EasySwoole\MQTT\Protocol\Message;
use EasySwoole\MQTT\Protocol\Reply\ConAck;
use EasySwoole\MQTT\Protocol\Reply\PubComp;
use EasySwoole\MQTT\Protocol\Reply\Reply;
use Swoole\Server;
use Swoole\Table;

class MQTT
{
    /** @var EventInterface */
    private $event;
    /** @var CacheInterface */
    private $cache;

    function setCache(CacheInterface $cache):MQTT
    {
        $this->cache = $cache;
        return $this;
    }

    function setEvent(EventInterface $event):MQTT
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @param Server|Server\Port $server
     */
    function attachServer($server)
    {
        $server->set([
            'open_mqtt_protocol' => true
        ]);
        /*
         * 启动前检查event和cache 是否设置
         */
        $server->on('receive', function (Server $server, $fd, $reactorId, $data) {
            $message = new Message($data);
            if ($message->getCommand()) {
                $reply = null;
                switch ($message->getCommand()) {
                    case Message::CONNECT:{
                        $reply = $this->event->onConnect( $message, $fd);
                        break;
                    }
                    case Message::PUBLISH:
                        $reply = $this->event->onPublish( $message, $fd);
                        break;
                    case Message::PUBACK:

                        break;
                    case Message::PUBREC:

                        break;
                    case Message::PUBREL:
                        $reply = new PubComp($message);
                        break;
                    case Message::PUBCOMP:

                        break;
                    case Message::SUBSCRIBE:
                        $reply = $this->event->onSubscribe($message, $fd);
                        break;
                    case Message::SUBACK:

                        break;
                    case Message::UNSUBSCRIBE:
                        $reply = $this->event->onUnsubscribe($message, $fd);
                        break;
                    case Message::UNSUBACK:

                        break;
                    case Message::PINGREQ:
                        $reply = $this->event->onPingreq($message, $fd);
                        break;
                    case Message::PINGRESP:

                        break;
                    case Message::DISCONNECT:
                        $reply = $this->event->onDisconnect($message, $fd);
                        break;
                }

                if($reply instanceof Reply && !empty($reply->__toString())){
                    $server->send($fd,$reply->__toString());
                }

            } else {
                $server->close($fd);
            }
        });
    }
}
