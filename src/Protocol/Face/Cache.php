<?php


namespace EasySwoole\Mqtt\Protocol\Face;


interface Cache
{
    /**
     * 设置缓存
     * @param string $key 键
     * @param array $value 值
     * @param int $ttl 毫秒ms
     * @return mixed
     */
    public function set(string $key, array $value, $ttl = 0);

    /**
     * 获取缓存
     * @param string $key
     * @param string|null $default
     * @return array
     */
    public function get(string $key);

    /**
     * 设置多个缓存
     * @param array $keys
     * @return mixed
     */
    public function many(array $keys);

    /**
     * 删除
     * @param array|string $key 批量删除传入 array, 单一删除传入 string
     * @return mixed
     */
    public function del($key);


    /**
     * 获取后删除
     * @param string $key 批量获取后删除 多个传入 array, 单一传入 string
     * @return mixed
     */
    public function pull(string $key);
}