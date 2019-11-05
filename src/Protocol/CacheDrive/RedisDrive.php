<?php


namespace EasySwoole\Mqtt\Protocol\CacheDrive;


use EasySwoole\Mqtt\Protocol\Face\Cache;

class RedisDrive implements Cache
{
    /**
     * 设置缓存
     * @param string $key 键
     * @param mixed $value 值
     * @param int $ttl 毫秒ms
     * @return mixed
     */
    public function set(string $key, array $value, $ttl = 0)
    {
        // TODO: Implement set() method.
    }

    /**
     * 获取缓存
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function get(string $key)
    {
        // TODO: Implement get() method.
    }

    /**
     * 设置多个缓存
     * @param array $keys
     * @return mixed
     */
    public function many(array $keys)
    {
        // TODO: Implement many() method.
    }

    /**
     * 删除
     * @param array|string $key 批量删除传入 array, 单一删除传入 string
     * @return mixed
     */
    public function del($key)
    {
        // TODO: Implement del() method.
    }

    /**
     * 获取后删除
     * @param string $key 批量获取后删除 多个传入 array, 单一传入 string
     * @return mixed
     */
    public function pull(string $key)
    {
        // TODO: Implement pull() method.
    }
}