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
     * processes in the poll
     *
     * @var array
     */
    protected $processPool = [];

    public function execute(Process $process)
    {
    }

    /**
     * get the number of running process
     *
     * @return int
     */
    public function getRunningProcessesNumber()
    {
        return count($this->processPool);
    }

}