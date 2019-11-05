<?php


namespace EasySwoole\Mqtt\Protocol\Inherit;


use EasySwoole\Mqtt\Protocol\Face\Cache;

Abstract class AbstractTableCache implements Cache
{
    protected $table;

    /**
     * 设置缓存
     * @param string $key 键
     * @param string $value 值
     * @param int $ttl 毫秒ms
     * @return bool
     */
    public function set(string $key, $value, $ttl = null)
    {
        if(is_array($value)) {
            $value['create_time'] = time();
            $value['ttl'] = $ttl ?: 0;
        }

        return $this->table->set((string)$key, $value);
    }

    /**
     * 获取缓存
     * @param string $key
     * @return false|array
     */
    public function get(string $key)
    {
        return $this->table->get((string)$key);
    }

    /**
     * 设置多个缓存
     * @param array $keys ['key'=> 'value', 'key2' => 'value']
     * @return array
     */
    public function many(array $keys)
    {
        $result = [];
        foreach ($keys as $key => $value) {
            $result[$key] = $this->table->set((string)$key, ['value' => $value, 'add_time' => time()]);
        }
        return $result;
    }

    /**
     * 删除
     * @param array|string $key 批量删除传入 array, 单一删除传入 string
     * @return array|boolean
     */
    public function del($keys)
    {
        if(is_array($keys)) {
            foreach ($keys as $value) {
                $result[$value] = $this->table->del((string)$value);
            }
            return $result;
        }
        return $this->table->del((string)$keys);
    }

    /**
     * 获取后删除
     * @param string $key 批量获取后删除 多个传入 array, 单一传入 string
     * @return boolean|array
     */
    public function pull(string $key)
    {
        $data = $this->table->get((string)$key);
        if($data === false) {
            return $data;
        }
        $this->table->del((string)$key);
        return $data;
    }
}