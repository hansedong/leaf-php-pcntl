<?php

namespace Leaf\Managers\ProcessManager\Communication\UnixDomain;

/**
 * Class UninxDomain
 * 进程间通讯之：Unix域通讯
 */
class UninxDomainServer
{
    
    private $socketfile = '';
    private $clients = [];
    
    public function __construct($socketfile = '')
    {
        $this->socketfile;
    }
    
    /*
     * 服务端结束处理器
     * 主要作用：删除socket文件
     */
    public function shutdown()
    {
        if (file_exists($this->socketfile)) {
            unlink($this->socketfile);
        }
    }
    
    /**
     * 启动server
     *
     * @param string $type 启动的server类型，枚举值：tcp、udp。
     *
     * @return bool 启动成功返回true，失败返回false
     */
    public function start($type = 'udp')
    {
        $return = false;
        if ( !file_exists($this->socketfile)) {
            switch ($type) {
                case 'udp':
                    $return = $this->startUdpServer();
                    break;
                case 'tcp':
                    $return = $this->startTcpServer();
                    break;
                default:
                    $return = $this->startUdpServer();
            }
        }
        
        return $return;
    }
    
    /**
     * 启动UDP形式的Unix域服务端
     *
     * @return bool
     */
    public function startUdpServer()
    {
        $return = false;
        $socket = socket_create(AF_UNIX, SOCK_DGRAM, 'udp');
        $bindRes = socket_bind($socket, $this->socketfile);
        if ( !$bindRes) {
            return $bindRes;
        }
        $listenRes = socket_listen($socket, 9999999);
        if ( !$listenRes) {
            return $listenRes;
        }
        
        return $return;
    }
    
    /**
     * @return  bool
     */
    public function startTcpServer()
    {
        $return = false;
        
        return $return;
    }
    
}