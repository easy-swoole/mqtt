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
                        // 用户没有实现则默认ConAck
                        if (empty($this->event)) {
                            $reply = new ConAck($message);
                        } else {
                            $reply = $this->event->onConnect( $message, $fd);
                        }

                        $connctionInfo = $message->getConnectInfo();
                        $clientId = $connctionInfo['clientId'];
                        $protocolName = $connctionInfo['protocol'];
                        $protocolLevel = $connctionInfo['version'];
                        $reserved = $connctionInfo['reserved'];

                        // 如果同一客户端重复连接则拒绝
                        if ($this->cache->get($clientId)) {
                            $reply->setFlag(ConAck::REFUSE_SERVER_UNAVAILABLE);
                        }

                        // 协议名称
                        if ($protocolName !== 'MQTT') {
                            $reply->setFlag(ConAck::REFUSE_PROTOCOL);
                        }

                        // 协议级别
                        if ($protocolLevel !== 4) {
                            $reply->setFlag(ConAck::REFUSE_PROTOCOL);
                        }

                        // 连接标志 Connect Flags
                        if ($reserved !== 0) {
                            $reply->setFlag(ConAck::REFUSE_PROTOCOL);
                        }

                        // TODO: Clean Session

                        // TODO: Will Flag

                        // TODO: Qos Will Qos

                        // TODO: Will Retain

                        // TODO: User Name Flag

                        // TODO: Password Flag

                        // TODO: Keep Alive

                        // 连接成功则cache
                        if ($reply->getFlag() === ConAck::ACCEPT) {
                            $this->cache->set($clientId, []);
                        }
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
