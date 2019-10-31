<?php


namespace EasySwoole\Mqtt\Protocol;


class MessageServerEvent
{
    /**
     * 连接服务器
     */
    public function connectToServer($array)
    {

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