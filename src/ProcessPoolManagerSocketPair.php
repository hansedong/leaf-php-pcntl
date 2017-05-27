<?php

namespace Leaf\Managers\ProcessManager;

use Leaf\Managers\ProcessManager\Communication\SocketPair\SocketPair;
use Leaf\Managers\ProcessManager\Communication\SocketPair\SocketPipe;

/**
 * PHP的多进程,切记,不要在子进程中,用父进程已经初始化号的连接
 * Class ProcessPoolManager
 *
 * @package app\drivers
 */
class ProcessPoolManagerSocketPair
{
    /**
     * 进程池大小
     *
     * @var int
     */
    private $poolSize = 4;
    /**
     * 进程池中,要执行的回调任务
     *
     * @var callable
     */
    private $task = null;
    /**
     * 任务的参数
     *
     * @var array
     */
    private $callbackParams = [];
    /**
     * 所有子进程的写通道
     *
     * @var SocketPipe[]
     */
    private $socketPipes = [];
    /**
     * 主进程要发往子进程的数据
     * 注意：它可以是数组类型，也可以是协程类型，不管怎么这个熟悉是会遍历的，切遍历的value必须是字符串
     *
     * @var [string]array 数组类型，每个元素都必须是字符串，否则无法把字符串数据发往子进程
     */
    private $poolData = [];
    
    /**
     * 结束进程指令
     * 如果通过管道，接受到了结束进程指令，则子进程需要死掉
     *
     * @var string
     */
    private $killCmd = "861788d28f891f3396b8958a8f6923a0";
    
    /**
     * 执行进程任务
     *
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $return = false;
        //设置进程池
        if (empty($this->poolSize) || !is_callable($this->task)) {
            throw new \Exception("进程池有无效参数, 无法执行进程池任务!");
        }
        
        $pool = new ProcessPool($this->poolSize);
        $processNum = 0;
        while ($processNum < $this->poolSize) {
            $sockpair = new SocketPair();
            $process = new ProcessWithParams();
            $process->setParams(['socket' => $sockpair->getSideLeft()])->setTask($this->task);
            $process->socketPipe = $sockpair->getSideLeft();    //将socket追加为进程的一个熟悉
            $this->socketPipes[] = $sockpair->getSideRight();    //汇总所有sockets
            $process->killCmd = $this->killCmd;             //设置进程接收的KILL命令
            $pool->addProcess($process);
            $processNum++;
        }
        $pool->execute(false);
        //主进程发数据给子进程（如果子进程还没处理完，则发送操作会阻塞）
        if ( !empty($this->socketPipes)) {
            $socktsCopy = $this->socketPipes;
            foreach ($this->poolData as $data) {
                if (count($socktsCopy) == 0) {
                    $socktsCopy = $this->socketPipes;
                }
                $socketPipe = array_pop($socktsCopy);
                //如果发送的数据不是字符串，则转换为字符串
                $sendData = $data;
                if ( !is_string($data) && !empty($data)) {
                    $sendData = json_encode($data);
                }
                //发送数据
                if ( !empty($sendData)) {
                    $sendRes = $socketPipe->send($sendData);
                }
            }
            //遍历数据结束，则结束子进程
            /*（这种方式，由于要检测信号，造成脚本处理效率低下，所以不推荐使用）
            foreach ($pool->getProcesses() as $process) {
                $process->kill();
            }*/
            foreach ($this->socketPipes as $socket) {
                //发送结束指令，结束进程的执行
                $sendRes = $socket->send($this->killCmd);
            }
        }
        $pool->execute(true);
        
        return true;
    }
    
    /**
     * 设置进程池的大小
     *
     * @param int $poolSize 进程池中, 并行执行的任务数量
     * @param int $dataSize 进程池中, 每个任务要处理的数据量
     *
     * @return $this
     * @throws \Exception
     */
    public function setPoolSize($poolSize = 0)
    {
        if (empty($poolSize)) {
            throw new \Exception("进程池中并行进程数量不可设置为0或空, 每个进程任务要处理的数据量也不能为空");
        }
        $this->poolSize = $poolSize;
        
        return $this;
    }
    
    /**
     * 设置进程池的任务
     *
     * @param [] $call 进程池执行方法,数组类型, 第一个参数为对象,第二个参数为对象中的方法名
     */
    public function addPoolTask($call = [])
    {
        if (empty($call) || !is_callable($call)) {
            throw new \Exception("进程池任务无效");
        }
        /**
         * @param SocketPipe[] $arrParams
         */
        $callback = function ($arrParams = []) use ($call) {
            $socketPipe = ( !empty($arrParams['socket']) && is_resource($arrParams['socket']->getSocketItem()) && ($arrParams['socket'] instanceof SocketPipe)) ? $arrParams['socket'] : null;
            if ( !empty($socketPipe)) {
                while (1) {
                    $data = $socketPipe->receive();
                    //判断是否收到结束指令
                    if (rtrim($data) == $this->killCmd) {
                        exit();
                    } else {
                        call_user_func($call, $data);
                    }
                }
            }
        };
        $this->task = $callback;
        
        return $this;
    }
    
    /**
     * 设置进程池数据
     * 注意：该数据，必须是数组（元素为字符串），或者是一个协程迭代器，因为这个数据需要被遍历
     *
     * @return $this
     * @throws \Exception
     */
    public function setPoolData($data = [])
    {
        if ( !empty($data)) {
            //如果是数组
            if (is_array($data)) {
                $this->poolData = $data;
            } //如果是协程
            elseif (is_callable($data)) {
                $this->poolData = $data();
            }
        }
        
        return $this;
    }
    
    /**
     * 设置回调函数的参数
     *
     * @param array $params
     */
    public function setCallBackParams($params = [])
    {
        if ( !empty($params)) {
            $this->callbackParams = $params;
        }
        
        return $this;
    }
    
}