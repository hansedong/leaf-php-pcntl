<?php

namespace Leaf\Managers\ProcessManager;

use Leaf\Managers\ProcessManager\Base\ProcessBase;

/**
 * Class Process
 */
class ProcessPool
{
    
    //进程池大小（并行进程数量）
    private $poolSize = 0;
    //进程池中，所有"执行过的"进程id（注意：这里边不包含还未开始执行的进程任务）
    private $processPids = [];
    //进程池中，正在执行的任务大小
    private $aliveCount = 0;
    /**
     * 所有进程
     *
     * @var Process[]
     */
    private $processes = [];
    
    
    /**
     * 构造方法
     * Process constructor.
     */
    public function __construct($poolSize = 0)
    {
        $this->init();
        $this->setPoolSize($poolSize);
    }
    
    /**
     * 进程初始化
     */
    public function init()
    {
        //操作系统检测
        if ( !$this->checkIfPosixSys()) {
            throw new \Exception("pcntl is not supported on windows");
        }
    }
    
    /**
     * 检测是否为windows操作系统
     */
    private function checkIfPosixSys()
    {
        return (PHP_OS != 'Windows') ? true : false;
    }
    
    /**
     * 设置进程池大小
     * 默认情况下，进程池的大小为0，也就是不限制并行进程数，如果设置了大小，则进程池大小会固定在这个值上，超出的任务，不会执行，直到有其他进程任务执行完毕
     *
     * @param int $size
     *
     * @return $this
     */
    public function setPoolSize($size = 0)
    {
        $size = intval($size);
        if ($size > 0) {
            $this->poolSize = $size;
        }
        
        return $this;
    }
    
    /**
     * 执行进程任务
     * 注意：此执行为伪执行，实际上只是执行添加进程任务到池中，进程是否执行，在wait方法控制。
     *
     * @param Process|ProcessBase|ProcessWithParams $process
     *
     * @throws \Exception
     */
    public function addProcess($process)
    {
        if ($this->checkProcessValid($process) && $process->taskCheck()) {
            $this->processes[] = $process;
        } else {
            throw new \Exception("添加进程任务失败，进程任务，没有继承ProcessBase类");
        }
        
        return $this;
    }
    
    private function checkProcessValid($process)
    {
        $return = false;
        if ( !is_object($process)) {
            return $return;
        }
        $className = get_class($process);
        $class = new \ReflectionClass($className);
        $parentClassName = $class->getParentClass();
        if (isset($parentClassName->name) && ($parentClassName->name == ProcessBase::class)) {
            $return = true;
        }
        
        return $return;
    }
    
    /**
     * 更新已记录的pid
     *
     * @param int     $pid
     *
     * @param Process $process
     */
    private function addPid($pid)
    {
        $this->processPids[] = $pid;
    }
    
    /**
     * 杀死所有正在执行的任务
     * 通常来说，你可能用不到这个方法
     *
     * @return $this
     */
    public function kill()
    {
        
        return $this;
    }
    
    /**
     * 执行进程池中的所有任务
     * 默认情况下，进程池会等待池中所有进程任务执行完毕，如果$block设置为false，则主进程把所有进程任务开启后即退出
     *
     * @param bool $block  是否等待所有任务执行完毕
     * @param int  $usleep 执行检测的等待时间，$block为true时有效
     *
     * @return $this
     */
    public function execute($block = true, $usleep = 10)
    {
        //阻塞方式，按进程池方式处理
        if ($block) {
            do {
                usleep($usleep);
                foreach ($this->processes as $index => &$process) {
                    switch ($process->getProcessStatus()) {
                        case 0:
                            //进程池没有上限情况 或 进程池有上限但未到上限，才会执行
                            if (($this->poolSize == 0) || (($this->poolSize > 0) && $this->aliveCount < $this->poolSize)) {
                                $process->run();
                                $this->addPid($process->getPid());
                                $this->aliveCount++;
                            }
                            break;
                        case 1:
                            continue;
                            break;
                        case 2:
                            $this->aliveCount--;
                            unset($this->processes[$index]);
                            break;
                    }
                }
                
            } while ($this->aliveCount > 0);
        } //非阻塞方式，启动完所有进程任务，就return
        else {
            foreach ($this->processes as $index => &$process) {
                $process->run();
                $this->addPid($process->getPid());
            }
        }
        
        return $this;
    }
    
    
}