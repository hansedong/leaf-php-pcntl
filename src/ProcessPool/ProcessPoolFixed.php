<?php

namespace Leaf\Pcntl\ProcessPool;

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
     * if the numbers of processes if more than the fixed process number, the pool will wait for process
     *
     * @return $this
     */
    public function execute()
    {
        if ( !empty( $this->processPool )) {
            foreach ($this->processPool as $pid => $process) {
                if (( $process->getRunningStatus() === 0 )) {
                    $process->start();
                }
            }
        }

        return $this;
    }

    /**
     *
     */
    public function wait()
    {

    }

}