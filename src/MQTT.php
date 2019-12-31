<?php


namespace EasySwoole\MQTT;


use EasySwoole\MQTT\Protocol\Message;
use EasySwoole\MQTT\Protocol\Reply\ConAck;
use EasySwoole\MQTT\Protocol\Reply\Reply;
use Swoole\Server;
use Swoole\Table;

class MQTT
{
    private $event;
    private $clientInfo;

    function __construct(EventInterface $event,int $tableSize = 1024 * 128)
    {
        $this->event = $event;
        $this->clientInfo = new Table($tableSize);
        $this->clientInfo->column('auth', Table::TYPE_INT, 1);
        $this->clientInfo->create();
    }

    /**
     * @param Server|Server\Port $server
     */
    function attachServer($server)
    {
        $server->set([
            'open_mqtt_protocol' => true
        ]);
        $server->on('receive', function (Server $server, $fd, $reactorId, $data) {
            $message = new Message($data);
            if ($message->getCommand()) {
                $reply = null;
                if ($message->getCommand() === Message::CONNECT) {
                    $reply = $this->event->onConnect( $message, $fd);
                    if ($reply instanceof Reply) {
                        $this->connect($server, $fd, $reactorId, $message);
                    }
                } else {
                    $info = $this->clientInfo->get($fd);
                    if (empty($info)) {
                        return;
                    }
                    switch ($message->getCommand()) {
                        case Message::PUBLISH:
                            $this->publish($server, $fd, $reactorId, $message);
                            break;
                        case Message::PUBACK:

                            break;
                        case Message::PUBREC:

                            break;
                        case Message::PUBREL:

                            break;
                        case Message::PUBCOMP:

                            break;
                        case Message::SUBSCRIBE:

                            break;
                        case Message::SUBACK:

                            break;
                        case Message::UNSUBSCRIBE:

                            break;
                        case Message::UNSUBACK:

                            break;
                        case Message::PINGRESP:

                            break;
                        case Message::DISCONNECT:

                            break;
                    }
                }

            } else {
                $server->close($fd);
            }
        });
    }

    /**
     * 内部连接处理
     *
     * @param Server $server
     * @param $fd
     * @param $reactorId
     * @param $message
     * CreateTime: 2019/12/26 上午12:08
     */
    private function connect($server, $fd, $reactorId, $message)
    {
        $this->clientInfo->set($fd, ['auth' => 1]);
        $ack = new ConAck();
        $server->send($fd, $ack->__toString());
    }

    /**
     *
     *
     * @param Server $server
     * @param $fd
     * @param $reactorId
     * @param $message
     * CreateTime: 2019/12/26 上午12:11
     */
    private function publish($server, $fd, $reactorId, $message)
    {
        var_dump($message);
    }

    private function puback()
    {

    }

    private function pubrec()
    {

    }

    private function pubcomp()
    {

    }

    private function subscribe()
    {

    }

    private function suback()
    {

    }

    private function unsubscribe()
    {

    }

    private function unsuback()
    {

    }

    private function pingresp()
    {

    }

    private function disconnect()
    {

    }
}
