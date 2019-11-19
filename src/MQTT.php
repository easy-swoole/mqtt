<?php


namespace EasySwoole\MQTT;


use EasySwoole\MQTT\Protocol\Message;
use EasySwoole\MQTT\Protocol\Reply\Reply;
use Swoole\Server;

class MQTT
{
    private $event;

    function __construct()
    {
        $this->event = new Event();
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
            $parser = new Message($data);
            if($parser->getCommand()){
                $reply = $this->event()->hook($parser->getCommand(),$parser,$fd,$data);
                if($reply instanceof Reply){
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