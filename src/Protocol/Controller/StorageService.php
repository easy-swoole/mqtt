<?php


namespace EasySwoole\Mqtt\Protocol\Controller;


use EasySwoole\Mqtt\Protocol\Inherit\AbstractTableCache;
use EasySwoole\Mqtt\Protocol\Inherit\Singleton;

class StorageService extends AbstractTableCache
{
    use Singleton;

    private $maxLine = 1024*1792;
    protected $drive;

    private function __construct()
    {
        $tableManager = new TableManager();
        $tableManager->add('mqtt_cache', [
            'fd' => [
                'type' => TableManager::TYPE_STRING,
                'size' => 256
            ],
            'protocol' => [
                'type' => TableManager::TYPE_STRING,
                'size' => 256
            ],
            'version' => [
                'type' => TableManager::TYPE_INT,
                'size' => 8
            ],
            'auth' => [
                'type' => TableManager::TYPE_INT,
                'size' => 1
            ],
            'willRetain' => [
                'type' => TableManager::TYPE_INT,
                'size' => 1
            ],
            'willQos' => [
                'type' => TableManager::TYPE_INT,
                'size' => 1
            ],'willFlag' => [
                'type' => TableManager::TYPE_INT,
                'size' => 1
            ],
            'cleanSession' => [
                'type' => TableManager::TYPE_INT,
                'size' => 1
            ],'keepAlive' => [
                'type' => TableManager::TYPE_INT,
                'size' => 8
            ],
            'clientId' => [
                'type' => TableManager::TYPE_STRING,
                'size' => 256
            ],
            'willTopic' => [
                'type' => TableManager::TYPE_STRING,
                'size' => 256
            ],
            'willMessage' => [
                'type' => TableManager::TYPE_STRING,
                'size' => 256
            ],
            'username' => [
                'type' => TableManager::TYPE_STRING,
                'size' => 50
            ],
            'password' => [
                'type' => TableManager::TYPE_STRING,
                'size' => 256
            ],'create_time' => [
                'type' => TableManager::TYPE_INT,
                'size' => 8
            ],
            'over_time' => [
                'type' => TableManager::TYPE_INT,
                'size' => 8
            ],
        ], $this->maxLine);

        $this->table = $tableManager->get('mqtt_cache');
    }

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

}