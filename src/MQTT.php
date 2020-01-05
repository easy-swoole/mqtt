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
                        $clientIdFlag = $connctionInfo['clientId'];
                        $protocolNameFlag = $connctionInfo['protocol'];
                        $protocolLevelFlag = $connctionInfo['version'];
                        $reservedFlag = $connctionInfo['reserved'];
                        $cleanSessionFlag = $connctionInfo['cleanSession'];
                        $willFlagFlag = $connctionInfo['willFlag'];
                        $willQosFlag = $connctionInfo['willQos'];
                        $willRetainFlag = $connctionInfo['willRetain'];
                        $userNameFlag = $connctionInfo['willRetain'];
                        $passwordFlag = $connctionInfo['willRetain'];
                        $keepAlive = $connctionInfo['keepAlive'];

                        // 如果同一客户端重复连接则拒绝
                        if ($this->cache->get($clientIdFlag)) {
                            $reply->setFlag(ConAck::REFUSE_SERVER_UNAVAILABLE);
                        }

                        // 协议名称
                        if ($protocolNameFlag !== 'MQTT') {
                            $reply->setFlag(ConAck::REFUSE_PROTOCOL);
                        }

                        // 协议级别
                        if ($protocolLevelFlag !== 4) {
                            $reply->setFlag(ConAck::REFUSE_PROTOCOL);
                        }

                        // 连接标志
                        if ($reservedFlag !== 0) {
                            $reply->setFlag(ConAck::REFUSE_PROTOCOL);
                        }

                        // 清理会话
                        // 0: 表示创建一个持久会话，在客户端断开连接时，会话仍然保持并保存离线消息，直到会话超时注销。
                        // 1: 表示创建一个新的临时会话，在客户端断开时，会话自动销毁。
                        if ($cleanSessionFlag === 1) {

                        } else {

                        }

                        // 遗嘱标志
                        if ($willFlagFlag === 1) {
                            // 遗嘱（Will Message）消息必须被存储在服务端并且与这个网络连接关联,网络连接关闭时，服务端必须发布这个遗嘱消息，除非服务端收到DISCONNECT报文时删除了这个遗嘱消息
                        }

                        // 遗嘱QoS Will QoS
                        if ($willQosFlag === 0) {
                            //遗嘱QoS也必须设置为0(0x00)
                        } elseif ($willQosFlag === 1) {
                            // 遗嘱QoS的值可以等于0(0x00)，1(0x01)，2(0x02)
                        }

                        // 遗嘱保留
                        if ($willRetainFlag === 0) {
                            // 遗嘱保留被设置为0，服务端必须将遗嘱消息当作非保留消息发布
                        } elseif ($willRetainFlag === 1) {
                            // 如果遗嘱保留被设置为1，服务端必须将遗嘱消息当作保留消息发布
                        }

                        // 用户名标志
                        if ($userNameFlag === 0) {
                            // 如果用户名（User Name）标志被设置为0，有效载荷中不能包含用户名字段
                        } elseif ($userNameFlag === 1) {
                            // 如果用户名（User Name）标志被设置为1，有效载荷中必须包含用户名字段
                        }

                        // 密码标志
                        if ($passwordFlag === 0) {
                            // 如果密码（Password）标志被设置为0，有效载荷中不能包含密码字段
                        } elseif ($passwordFlag === 1) {
                            // 如果密码（Password）标志被设置为1，有效载荷中必须包含密码字段
                        }

                        // 保持连接

                        // 连接成功则cache
                        if ($reply->getFlag() === ConAck::ACCEPT) {
                            $this->cache->set($clientIdFlag, []);
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
