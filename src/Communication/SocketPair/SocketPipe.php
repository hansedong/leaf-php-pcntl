<?php

namespace Leaf\Managers\ProcessManager\Communication\SocketPair;

/**
 * Class SocketPair
 * 进程间通讯之：Unix域-SocketPair
 * 此类的实例化操作，只能在父进程中执行，因为要保证父子进程通信，另外这种通信方式，必须是亲缘进程
 */
class SocketPipe
{
    
    /**
     * * @var resource socket管道
     */
    private $socket = null;
    
    private $dataBoundary = "\r";
    
    /**
     * @param null $socket
     *
     * @return $this
     */
    public function setSocket($socket = null)
    {
        if (is_resource($socket)) {
            $this->socket = $socket;
        } else {
            throw  new \Exception("设置管道socket失败，无效的资源类型");
        }
        
        return $this;
    }
    
    /**
     * 设置数据边界
     *
     * @param string $char
     *
     * @return bool
     */
    public function setDataBoundary($char = "\0")
    {
        $return = false;
        if ( !empty($char)) {
            $this->dataBoundary = $char;
        }
        
        return $return;
    }
    
    /**
     * 发送数据
     *
     * @return bool
     */
    public function send($data = '')
    {
        $return = false;
        if ( !empty($data) && is_resource($this->socket) && is_string($data)) {
            //补全换行符
            $data .= $this->dataBoundary;
            $size = socket_write($this->socket, $data, strlen($data));
            if ( !empty($size)) {
                $return = true;
            }
        } else {
            throw  new \Exception("无法发送数据：数据为空，或数据不为字符串，或socket不为资源类型");
        }
        
        return $return;
        
    }
    
    /**
     * 接收数据
     *
     * @return bool|string 成功返回字符串，失败返回false
     * @throws \Exception
     */
    public function receive()
    {
        $return = false;
        if (is_resource($this->socket)) {
            $return = socket_read($this->socket, 1024 * 1024 * 10, PHP_NORMAL_READ);
        } else {
            throw  new \Exception("无法发送数据：数据为空，或数据不为字符串，或socket不为资源类型");
        }
        
        return $return;
        
    }
    
    public function getSocketItem()
    {
        return $this->socket;
    }
    
}