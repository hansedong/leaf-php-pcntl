<?php

namespace Leaf\Managers\ProcessManager\Communication\UnixDomain;

/**
 * Class UninxDomain
 * 进程间通讯之：Unix域通讯
 */
class UninxDomainManager
{
    private $dir = '';
    private $socketfile = '';
    private $serverInstance = 0;
    
    private function __construct($dir = '', $filename = '')
    {
        $this->setUnixDomainDir($dir);
        if ( !$this->setUnixDomainFile($filename)) {
            throw new \Exception("Unix域文件已存在，无法继续");
        }
    }
    
    public function getInstance($dir = '', $filename = '')
    {
        return new static($dir, $filename);
    }
    
    /**
     * 设置Unix域socket文件路径
     * 注意：仅仅是设置路径，请不要再之前实现创建文件，系统会根据你设置的路径，自动创建socket文件
     *
     * @param string $strPath
     */
    public function setUnixDomainDir($dir = '')
    {
        if ( !empty($dir) || !is_string($dir)) {
            $dir = "/dev/shm/";
        }
        if ( !file_exists($dir) || !is_dir($dir)) {
            throw new \Exception("Unix域文件指定的目录不存在，无法继续");
        }
        $this->dir = $dir;
        
        return true;
    }
    
    /**
     * 设置Unix域socket文件路径
     * 注意：仅仅是设置路径，请不要再之前实现创建文件，系统会根据你设置的路径，自动创建socket文件
     *
     * @param string $strPath
     */
    public function setUnixDomainFile($filename = '')
    {
        $return = false;
        if (empty($filename)) {
            $filename = 'socket_' . getmypid() . '_' . time();
        }
        $path = $this->dir . '/' . $filename;
        if ( !file_exists($path)) {
            $this->socketfile = $path;
            $return = true;
        }
        
        return $return;
    }
    
    /**
     * 创建一个server
     * 创建成功，返回 UninxDomainServer 对象，否则返回 null。一个 UninxDomainManager 只能
     *
     * @return UninxDomainServer|null
     */
    public function newServer()
    {
        if ($this->serverInstance > 0) {
            return null;
        }
        $this->serverInstance = 1;
        
        $server = new UninxDomainServer($this->socketfile);
        
        return $server;
    }
    
    /**
     * 创建一个client
     *
     * @return UninxDomainClient
     */
    public function newClient()
    {
        $client = new UninxDomainClient($this->socketfile);
        
        return $client;
    }
    
}