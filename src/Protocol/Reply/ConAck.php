<?php


namespace EasySwoole\Mqtt\Protocol\Reply;

/**
 * 连接确认
 * @package EasySwoole\Mqtt\Protocol\Reply
 */
class ConAck extends Reply
{
    const ACCEPT = 0x00;
    const REFUSE_PROTOCOL = 0x01;
    const REFUSE_IDENTIFIER = 0x02;
    const REFUSE_SERVER_UNAVAILABLE = 0x03;
    const REFUSE_AUTH_FAIL = 0x04;
    const REFUSE_NOT_AUTH = 0x05;

    private $flag = self::ACCEPT;

    /**
     * 连接确认
     * todo 会话保持记录协议功能实现
     * @var integer
     */
    private $connectConfirm = 0x01;

    /**
     * 设置连接返回状态
     *
     * @param $flag
     * @return $this
     */
    function setFlag($flag)
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * 清理回话记录(不将历史未读消息推送回去)
     * todo 清理会话协议功能实现
     */
    function cleanSession()
    {
        $this->connectConfirm = 0x00;

        return $this;
    }

    function __toString()
    {
       return chr(0x20) . chr(0x02) //固定报头
           . chr($this->connectConfirm) //连接确认标志 位7-1是保留位且必须设置为0
           . chr($this->flag); //连接返回码
    }
}