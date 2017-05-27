<?php

namespace Leaf\Managers\ProcessManager\Communication\SocketPair;

/**
 * Class SocketPair
 * 进程间通讯之：Unix域-SocketPair
 * 此类的实例化操作，只能在父进程中执行，因为要保证父子进程通信，另外这种通信方式，必须是亲缘进程
 */
class SocketPair
{
    
    /**
     * @var SocketPipe[]
     */
    private $socketPair = [];
    
    public function __construct()
    {
        $this->initSocketPair();
    }
    
    private function initSocketPair()
    {
        $socketPair = [];
        $res = socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $socketPair);
        if ($res && !empty($socketPair) && count($socketPair) == 2) {
            $packSock = [
                (new SocketPipe())->setSocket($socketPair[0]),
                (new SocketPipe())->setSocket($socketPair[1]),
            ];
            $this->socketPair = $packSock;
        } else {
            $msg = "socket_create_pair failed. Reason: " . socket_strerror(socket_last_error());
            throw new \Exception($msg);
        }
    }
    
    /**
     * 获取左侧管道端点
     *
     * @return SocketPipe|null
     */
    public function getSideLeft()
    {
        $return = null;
        if ( !empty($this->socketPair) && count($this->socketPair) == 2) {
            return $this->socketPair[0];
        }
        
        return $return;
    }
    
    /**
     * 获取右侧管道端点
     *
     * @return SocketPipe|null
     */
    public function getSideRight()
    {
        $return = null;
        if ( !empty($this->socketPair) && count($this->socketPair) == 2) {
            return $this->socketPair[1];
        }
        
        return $return;
    }
    
}