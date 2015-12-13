<?php

namespace Leaf\Pcntl\ProcessPool\Base;

use Leaf\Pcntl\Process;

/**
 * Class ProcessPoolInterface
 *
 * @package Leaf\ProcessPool\ProcessPool
 */
abstract class ProcessPoolAbstract implements ProcessPoolInterface
{

    /**
     * @var array Process
     */
    protected $processPool = [];
    
    /**
     * Put a process in the pool, then you can start them by self::execute
     *
     * @param Process $process
     *
     * @return $this
     */
    public function addProcess(Process $process)
    {
        //check if the process has the correct pid
        $pid = $process->getPid();
        if ( !empty( $pid )) {
            $this->processPool[$pid] = $process;
        }
        else {
            throw new \InvalidArgumentException('the process is invaliad!');
        }

        return $this;
    }

    /**
     * execute the process pool
     *
     * @return mixed
     */
    public function execute()
    {

    }

    /**
     * wait for children process to exit
     */
    public function wait()
    {

    }

}