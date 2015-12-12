<?php

namespace Leaf\Pcntl\ProcessPool;

use Leaf\Pcntl\Process;

/**
 * Class ProcessPoolInterface
 *
 * @package Leaf\ProcessPool\ProcessPool
 */
interface ProcessPoolInterface
{
    /**
     * add process to this pool
     *
     * @param Process $process
     *
     * @return $this
     */
    public function addProcess(Process $process);

    /**
     * execute the process pool
     *
     * @return mixed
     */
    public function execute();

}