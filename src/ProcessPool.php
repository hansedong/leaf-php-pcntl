<?php

namespace Leaf\Pcntl;

/**
 * Class ProcessPool
 * process pool manager. It's designed as PHP-FPM. you can set a min/max child processes for this manager for the
 * manager dynamically or a fixed of child processes staticly
 *
 * @package Leaf\Pcntl
 */
class ProcessPool
{
    /**
     * the min child processes
     * the number of child processes are set dynamically
     *
     * @var int
     */
    protected $minProcessNum = 2;

    /*
     * the min child processes
     * the number of child processes are set dynamically
     */
    protected $maxProcessNum = 5;

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

    protected $processMode = '';


    public function addProcess(Process $process)
    {

    }

}