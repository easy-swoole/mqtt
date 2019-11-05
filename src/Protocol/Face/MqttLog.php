<?php


namespace EasySwoole\Mqtt\Protocol\Face;

/**
 * Mqtt 日志
 * @package EasySwoole\Mqtt\Protocol\Face
 */
interface MqttLog
{
    /**
     * 其他 日志
     * @return mixed
     */
    public function normall($data);

    /**
     * 错误日志
     * @param \Exception $exception
     * @return mixed
     */
    public function error(\Exception $exception);

    /**
     * 警告日志
     * @param \Exception $exception
     * @return mixed
     */
    public function warn(\Exception $exception);

}