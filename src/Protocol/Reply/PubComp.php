<?php
/**
 * @CreateTime:   2019/12/29 下午7:28
 * @Author:       huizhang  <tuzisir@163.com>
 * @Copyright:    copyright(2019) Easyswoole all rights reserved
 * @Description:  发布完成
 */
namespace EasySwoole\MQTT\Protocol\Reply;

class PubComp extends Reply
{
    function __toString()
    {
        return chr(0x70) . chr(0x02) . $this->parser->getPacketId();
    }
}
