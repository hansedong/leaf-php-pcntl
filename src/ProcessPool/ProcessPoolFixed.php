<?php

namespace Leaf\Pcntl\ProcessPool;

use Leaf\Pcntl\Process;
use Leaf\Pcntl\ProcessPool\Base\ProcessPoolAbstract;

/**
 * Class ProcessPoolFixed
 * it is a process pool with a fixed number, you can use it when you have some one time tasks. it's not applicable for
 * background workers such as queue workers.
 *
 * @package Leaf\Pcntl\ProcessPool
 */
class ProcessPoolFixed extends ProcessPoolAbstract
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
     * set the fixed process number of the pool
     *
     * @param int $num
     *
     * @return $this
     */
    public function setFixedProcessNumber($num)
    {
        if ( !empty( $num ) && is_int($num)) {
            $this->fixedProcessNum = $num;
        }

        return $this;
    }

    /**
     * start all the processes in the pool with a fixed number
     * if the number of processes is more than the fixed process number, the pool will wait until it less than the
     * fixed number
     * you should be clear that this method aims to start the process's task, not to start the process
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
     *
     * @param int $sleep
     */
    public function wait($sleep = 200)
    {
        while (count($this->processPool) > 0) {
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