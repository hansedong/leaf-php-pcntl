<?php

namespace Leaf\Pcntl\ProcessPool\Base;

use Leaf\Pcntl\Process;

/**
 * Class ProcessPoolInterface
 *
 * @package Leaf\ProcessPool\ProcessPool
 */
interface ProcessPoolInterface
{
    /**
     * execute the process pool
     *
     * @return mixed
     */
    public function execute(Process $process);

    public function getRunningProcessesNumber();


}