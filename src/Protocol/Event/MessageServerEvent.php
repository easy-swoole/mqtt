<?php


namespace EasySwoole\Mqtt\Protocol\Event;


use EasySwoole\Mqtt\Protocol\Inherit\Event\AbstractConnectionServer;
use EasySwoole\Mqtt\Protocol\Inherit\Singleton;
use EasySwoole\Mqtt\Protocol\Message;
use EasySwoole\Mqtt\Protocol\Reply\ConAck;

class MessageServerEvent
{
    use Singleton;

    /**
     * 连接服务器
     * @param Message $message
     * @param AbstractConnectionServer $eventObj
     * @return string
     */
    public function connectToServer(Message $message, AbstractConnectionServer $eventObj)
    {


        //清理会话 检测

        //遗嘱标志

        //遗嘱QoS
        //遗嘱保留

        //用户名标志

        //密码标志

        //保持连接

        $replyObj = new ConAck();


        //0	0x00连接已接受	连接已被服务端接受
        //1	0x01连接已拒绝，不支持的协议版本	服务端不支持客户端请求的MQTT协议级别
        //2	0x02连接已拒绝，不合格的客户端标识符	客户端标识符是正确的UTF-8编码，但服务端不允许使用
        //3	0x03连接已拒绝，服务端不可用	网络连接已建立，但MQTT服务不可用
        //4	0x04连接已拒绝，无效的用户名或密码	用户名或密码的数据格式无效
        //5	0x05连接已拒绝，未授权	客户端未被授权连接到此服务器


        return (string)$replyObj;
    }


    /**
     * 确认连接请求
     */
    public function confirmConnectionRequest()
    {

    }

    /**
     * 发布消息
     */
    public function partyMessage()
    {

    }

    /**
     * 发布确认
     */
    public function releaseConfirmation()
    {

    }

    /**
     * 发布收到
     */
    public function releaseReceiv()
    {

    }

    /**
     * 发布释放
     */
    public function releaseRelease()
    {

    }

    /**
     * 发布完成
     */
    public function releaseToComplete()
    {

    }


    /**
     * 订阅主题
     */
    public function subscribeToTopic()
    {

    }

    /**
     * 订阅确认
     */
    public function subscriptionConfirmation()
    {

    }

    /**
     * 取消订阅
     */
    public function cancelSubscription()
    {

    }

    /**
     * 取消订阅确认
     */
    public function cancelSubscriptionConfirmation()
    {

    }

    /**
     * 心跳请求
     */
    public function heartRateRequest()
    {

    }

    /**
     * 心跳响应
     */
    public function heartRateResponse()
    {

    }

    /**
     * 断开连接
     */
    public function disconnect()
    {

    }
}