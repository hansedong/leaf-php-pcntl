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
     * add process to this pool
     *
     * @param Process $process
     *
     * @return $this
     */
    public function addProcess(Process $process)
    {

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