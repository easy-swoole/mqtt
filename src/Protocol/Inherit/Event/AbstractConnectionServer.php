<?php


namespace EasySwoole\Mqtt\Protocol\Inherit\Event;

use EasySwoole\Mqtt\Protocol\Inherit\Singleton;

/**
 * 连接 Server 事件
 * @package EasySwoole\Mqtt\Protocol\Inherit
 */
Abstract class AbstractConnectionServer
{
    use Singleton;
    /**
     * 是否开启客户端安全校验
     * 当为 true 时  客户端连接必须有 auth 类型连接
     * @var bool
     */
    public $auth = false;

    /**
     * 当此方法返回值为 false 时, 会直接拒绝
     * 0x05
     * @return bool  true 为跳过 false 为执行此状态返回
     */
    public function whetherToAcceptConnection()
    {
        return true;
    }

    /**
     * 客户端 auth 类型连接校验
     * 0x04
     * @return  bool true 为跳过 false 为执行此状态返回
     */
    public function auth()
    {
        return true;
    }

    /**
     * 网络连接已建立，但MQTT服务不可用
     *
     * 0x03
     * @return bool true 为跳过 false 为执行此状态返回
     */
    public function serverIsNotAvailable()
    {
        return true;
    }


}