<?php

namespace Leaf\Managers\ProcessManager;

/**
 * PHP的多进程,切记,不要在子进程中,用父进程已经初始化号的连接
 * Class ProcessPoolManager
 *
 * @package app\drivers
 */
class ProcessPoolManagerChunk
{
    private $arrChunkData = [];     //进程池要处理的总数据
    private $poolSize = 4;          //进程池大小
    private $dataSize = 500;        //每个进程要处理的数据量
    private $task = [];             //进程池中,要执行的回调任务
    private $callbackParams = [];   //任务的参数
    
    /**
     * 执行进程任务
     *
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $return = true;
        //设置进程池
        if (empty($this->arrChunkData) || empty($this->poolSize) || empty($this->dataSize) || !is_callable($this->task)) {
            throw new \Exception("进程池有无效参数, 无法执行进程池任务!");
        }
        $arrTaskData = array_chunk($this->arrChunkData, $this->dataSize);
        $pool = new ProcessPool($this->poolSize);
        foreach ($arrTaskData as $data) {
            $process = new ProcessWithParams();
            //将回调函数要处理的数据和可以接受的参数,作为整体参数,传递到任务中
            $params = array_merge($this->callbackParams, [$data]);
            $process->setParams($params)->setTask($this->task);
            $pool->addProcess($process);
        }
        $pool->execute(true);
        
        return $return;
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
    public function setPoolSize($poolSize = 0, $dataSize = 500)
    {
        if (empty($poolSize) || empty($dataSize)) {
            throw new \Exception("进程池中并行进程数量不可设置为0或空, 每个进程任务要处理的数据量也不能为空");
        }
        $this->poolSize = $poolSize;
        $this->dataSize = $dataSize;
        
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
        $this->task = $call;
        
        return $this;
    }
    
    /**
     * 设置回调函数的参数
     *
     * @param array $params
     *
     * @return $this
     */
    public function setCallBackParams($params = [])
    {
        if ( !empty($params)) {
            $this->callbackParams = $params;
        }
        
        return $this;
    }
    
    /**
     * 设置进程池要处理的总数据
     * 注意, 分配到单个进程的数据, 会是参数$arrData的子集
     *
     * @param [] $arrData 要分块处理的数据
     *
     * @return $this
     * @throws \Exception
     */
    public function setChunkData($arrData = [])
    {
        if (empty($arrData) || !is_array($arrData)) {
            throw new \Exception("进程池处理数据不能为空");
        }
        $this->arrChunkData = $arrData;
        
        return $this;
    }
    
}