<?php


namespace EasySwoole\MQTT;


use Swoole\Table;

class TableCache implements CacheInterface
{
    private $table;
    function __construct(int $size = 1024 * 128)
    {
        $this->table = new Table($size);
        $this->table->column('data', Table::TYPE_STRING, 128);
        $this->table->create();
    }

    function set(string $key, $data)
    {
        return $this->table->set($key,['data'=>serialize($data)]);
    }

    function get(string $key)
    {
        $info = $this->table->get($key);
        if($info){
            return unserialize($info['data']);
        }
        return null;
    }

    function delete(string $key)
    {
        return $this->table->del($key);
    }
}
