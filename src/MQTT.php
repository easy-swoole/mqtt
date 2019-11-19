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

    function __construct(int $tableSize = 1024*128)
    {
        $this->event = new Event();
        $this->clientInfo = new Table($tableSize);
        $this->clientInfo->column('auth',Table::TYPE_INT,1);
        $this->clientInfo->create();
    }

    function event():Event
    {
        return $this->event;
    }

    /**
     * @param Server|Server\Port $server
     */
    function attachServer($server)
    {
        $server->set([
            'open_mqtt_protocol'=>true
        ]);
        $server->on('receive',function (Server $server, $fd, $reactor_id, $data){
            $message = new Message($data);
            if($message->getCommand()){
                $reply = null;
                if($message->getCommand() != Message::CONNECT){
                    $info = $this->clientInfo->get($fd);
                    if(empty($info)){
                        /*
                         * 判断当前fd是否已经允许链接了,除了CONNECT包，其他的包都应该在CONNECT行为后，
                         * 若当前fd没有经过链接行为确认，则禁止执行其他行为
                        */
                        return;
                    }
                }
                $reply = $this->event()->hook($message->getCommand(),$message,$server,$fd);
                if($reply instanceof Reply){
                    if(($message instanceof ConAck) && ($message->getFlag() == $message::ACCEPT)){
                        $this->clientInfo->set($fd,['auth'=>1]);
                    }
                    $server->send($fd,(string)$reply);
                }
            }else{
                /*
                 * 关闭恶意数据包
                 */
                $server->close($fd);
            }
        });
    }
}