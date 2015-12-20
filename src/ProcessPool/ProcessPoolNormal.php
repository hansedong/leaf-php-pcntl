<?php

namespace Leaf\Pcntl\ProcessPool;

use Leaf\Pcntl\Process;
use Leaf\Pcntl\ProcessPool\Base\ProcessPoolAbstract;

/**
 * Class ProcessPoolNormal
 * it is a process pool with a fixed number, you can use it when you have some one time tasks. it's not applicable for
 * background workers such as queue workers.
 *
 * @package Leaf\Pcntl\ProcessPool
 */
class ProcessPoolNormal extends ProcessPoolAbstract
{

    /**
     * the fixed number of child process
     * a fixed number of child processes
     *
     * @var int
     */
    protected $fixedProcessNum = 5;

    /**
     * the current number of processes
     *
     * @var int
     */
    protected $currentProcessNum = 0;

    /**
     * put a process into the pool and then start it immediately
     *
     * @param Process $process
     *
     * @return $this
     */
    public function execute(Process $process)
    {
        //check the running status of the process
        if ($process->getRunningStatus() === 0 && is_callable($process->getTask())) {
            $pid = $process->start();
            $this->processPool[$pid] = $pid;
        }
        elseif ($process->getRunningStatus() === 1) {
            throw new \InvalidArgumentException('the process is invalid: it is already running!');
        }
        elseif ( !is_callable($process->getTask())) {
            throw new \InvalidArgumentException('the process is invalid: the process has a invalid callback task!');
        }

        return $this;
    }

    /**
     * wait until all the children processes are exited
     * when this method called, the parent process will wait until all the children processes are exited, it checks
     * the alive number of processes per 200ms as default
     *
     * @param int $sleep
     */
    public function wait($sleep = 200)
    {
        while ($this->getRunningProcessesNumber() > 0) {
            foreach ($this->processPool as $key => $pid) {
                $res = pcntl_waitpid($pid, $status, WNOHANG);
                // If the process has already exited
                if ($res == -1 || $res > 0) {
                    unset( $this->processPool[$key] );
                }
            }

            usleep($sleep);
        }
    }

}