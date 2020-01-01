<?php
namespace EasySwoole\MQTT;

interface CacheInterface
{
    function set(string $key,$data);
    function get(string $key);
    function delete(string $key);
}
