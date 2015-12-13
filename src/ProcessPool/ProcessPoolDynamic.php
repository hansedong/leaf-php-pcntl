<?php

namespace Leaf\Pcntl\ProcessPool;

use Leaf\Pcntl\Process;
use Leaf\Pcntl\ProcessPool\Base\ProcessPoolAbstract;

/**
 * Class ProcessPoolFixed
 * process pool manager. It's designed as PHP-FPM. you can set  a fixed number of  child processes
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
    protected $fixedProcessNum = 2;

    /**
     * the current number of processes
     *
     * @var int
     */
    protected $currentProcessNum = 0;

    /**
     * @var array
     */
    protected $processPool = [];

    public function addProcess(Process $process)
    {
        //check if the process has the correct pid
        if ( !empty( $process->getPid() )) {
            $this->pool[] = $process;
        }
        else {
            throw new \InvalidArgumentException('the process is invaliad!');
        }
    }

    public function execute()
    {

    }

}