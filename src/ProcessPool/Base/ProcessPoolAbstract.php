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
     * Put a process in the pool, and then execute the process
     *
     * @return mixed
     */
    public function execute(Process $process)
    {

    }

    /**
     * wait for children process to exit
     */
    public function wait()
    {

    }

}