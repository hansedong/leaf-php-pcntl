<?php

namespace Leaf\Pcntl\ProcessPool;

use Leaf\Pcntl\Process;
use Leaf\Pcntl\ProcessPool\Base\ProcessPoolAbstract;

/**
 * Class ProcessPoolStatic
 * it is a process pool with a fixed number, you can use it when you have some one time tasks. it's not applicable for
 * background workers such as queue workers.
 *
 * @package Leaf\Pcntl\ProcessPool
 */
class ProcessPoolStatic extends ProcessPoolAbstract
{

    /**
     * the fixed number of child process
     * a fixed number of child processes
     *
     * @var int
     */
    protected $fixedProcessNum = 5;

    /**
     * process int the delay process pool
     * as the pool dynamic or static, this pool contains the processes to be executed delayed
     *
     * @var array
     */
    protected $delayedProcesses = [];

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
     * put a process into the pool and then start it, it may not start immediately
     */
    public function addProcess(Process $process)
    {
        //check the running status of the process
        if ($process->getRunningStatus() === 0 && is_callable($process->getTask())) {
            $this->delayedProcesses[] = $process;
        }
        elseif ($process->getRunningStatus() === 1) {
            throw new \InvalidArgumentException('the process is invalid: it is already running!');
        }
        elseif ( !is_callable($process->getTask())) {
            throw new \InvalidArgumentException('the process is invalid: the process has a invalid callback task!');
        }

        return $this;
    }

    /**
     * if the number of processes is more than the fixed process number, the pool will wait until it less than the
     * fixed number
     *
     * @param Process $process
     *
     * @return $this
     */
    public function execute(Process $process)
    {
        //check the running status of the process
        if ($process->getRunningStatus() === 0 && is_callable($process->getTask())) {
            $pid = $process->start();
            $this->processPool[$pid] = $pid;
        }
        elseif ($process->getRunningStatus() === 1) {
            throw new \InvalidArgumentException('the process is invalid: it is already running!');
        }
        elseif ( !is_callable($process->getTask())) {
            throw new \InvalidArgumentException('the process is invalid: the process has a invalid callback task!');
        }

        return $this;
    }

    /**
     * wait until all the children processes are exited
     * when this method called, the parent process will wait until all the children processes are exited, it checks
     * the alive number of processes per 100ms as default
     * if the number of all the process is more than the fixed number of the pool, the pool will execute a process
     * when it is less than the fixed number to keep balance
     *
     * @param int $sleep
     */
    public function run($sleep = 100)
    {
        while (( $this->getRunningProcessesNumber() > 0 ) || ( $this->getDelayedProcessesNumber() > 0 )) {
            //unset the children process that exited
            if ($this->getRunningProcessesNumber() > 0) {
                foreach ($this->processPool as $key => $pid) {
                    $res = pcntl_waitpid($pid, $status, WNOHANG);
                    // If the process has already exited
                    if ($res == -1 || $res > 0) {
                        unset( $this->processPool[$key] );
                    }
                }
            }
            if (( $this->getDelayedProcessesNumber() > 0 ) && ( $this->getRunningProcessesNumber() < $this->fixedProcessNum )) {
                $process = array_shift($this->delayedProcesses);
                $this->execute($process);
            }

            usleep($sleep);
        }
    }

    /**
     * get the process number which need to be executed delayed
     *
     * @return int
     */
    public function getDelayedProcessesNumber()
    {
        return count($this->delayedProcesses);
    }

    /**
     * run this pool as a deamon
     */
    public function runAsDaemon()
    {
        $pid = pcntl_fork();
        if ($pid == 0) {
            $this->run();
        }
        else {
            exit( 0 );
        }
    }

}