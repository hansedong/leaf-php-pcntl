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
class ProcessPoolDynamic extends ProcessPoolAbstract
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

}