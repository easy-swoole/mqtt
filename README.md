# Easyswoole-Mqtt

## 原生使用说明
- 创建 tcp 服务
- 设置 `swoole_server` 配置中 `open_mqtt_protocol` 配置为 `true`
- 加入以下代码
```php 
use EasySwoole\Mqtt\Protocol\MqttServerEvent;


/** @var \swoole_server $swooleSwoole */
$mqttServerEvent = MqttServerEvent::getInstance($swooleSwoole);
```

##  Easyswoole 使用

可在主服务创建事件中加入

会自动配置服务 `open_mqtt_protocol` 为 `true`

```php
public static function mainServerCreate(EventRegister $register)
{
    $server = ServerManager::getInstance()->getSwooleServer();
    $mqttServer = MqttServerEvent::getInstance($server);
}
```

### 新端口开启

单独端口开启TCP服务器，需要添加子服务。

```php
public static function mainServerCreate(EventRegister $register)
{
    $server = ServerManager::getInstance()->getSwooleServer();
    $newServer = $server->addlistener('0.0.0.0', 9502, SWOOLE_TCP);
    if($newServer === false) {
        $errorCode = $newServer->getLastError(); //https://wiki.swoole.com/wiki/page/554.html
    } else {
        $mqttServer = MqttServerEvent::getInstance($newServer);
    }
}

```

## 事件注册

```php
/**
 * 消息类型对应不同的事件
 * @var array
 */
private $on = [
    'connectToServer' => ConnectToServerEvent::class,
    'confirmConnectionRequest' => '',
    'partyMessage' => '',
    'releaseConfirmation' => '',
    'releaseReceiv' => '',
    'releaseRelease' => '',
    'releaseToComplete' => '',
    'subscribeToTopic' => '',
    'subscriptionConfirmation' => '',
    'cancelSubscription' => '',
    'cancelSubscriptionConfirmation' => '',
    'heartRateRequest' => '',
    'heartRateResponse' => '',
    'disconnect' => '',
];
```

Mqtt 有不同事件,你可以在协议处理过程插入自定义处理事件. 

详细的处理事件需要继承并重写响应的抽象方法 `vendor/easyswoole/mqtt/src/Protocol/Inherit/Event`

默认使用自带的抽象事件实现.

推荐在重写方法后在服务注册后通过 `on()` 方法更改事件

```php
$mqttServer->on('connectToServer', EasySwoole\Mqtt\Protocol\Event\DefaultEvent\ConnectToServerEvent::class);
```

## 缓存驱动

实现 `vendor/easyswoole/mqtt/src/Protocol/Face/Cache.php` 接口

默认使用自提供驱动

```php
$mqttServerEvent->setCacheDrive(EasySwoole\Mqtt\Protocol\CacheDrive\RedisDrive::class);
```

## 参考文献
- https://mcxiaoke.gitbooks.io/mqtt-cn/content/mqtt/01-Introduction.html