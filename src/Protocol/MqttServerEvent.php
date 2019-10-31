<?php


namespace EasySwoole\Mqtt\Protocol;


class MqttServerEvent
{
    private $port;

    /**
     * MqttServerEvent constructor.
     * @param mixed $port
     */
    public function __construct($port)
    {
        $this->port = $port;
        $this->setConf();
        $this->initOn();

        return $this;
    }

    private function setConf()
    {
        $this->getServer()->set(
            [
                'open_mqtt_protocol' => true,
            ]
        );
    }

    public function getServer()
    {
        return $this->port;
    }

    private function initOn()
    {
        $this->getServer()->on('connect', function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "fd:{$fd} 已连接\n";
            $str = '恭喜你连接成功';
            $server->send($fd, $str);
        });
        $this->getServer()->on('close', function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "fd:{$fd} 已关闭\n";
        });
        $this->getServer()->on('receive', function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
            echo "fd:{$fd} 发送消息:{$data}\n";
        });
    }

}