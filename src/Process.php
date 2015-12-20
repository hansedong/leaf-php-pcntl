<?php

namespace Leaf\Pcntl;

/**
 * Class Process
 * process
 *
 * @package Leaf\Pcntl
 */
class Process
{

    /**
     * the pid of this process
     *
     * @var int
     */
    protected $pid = 0;

    /**
     * the task to be executed of this process. it's a callback concret
     *
     * @var $mixed
     */
    protected $task = null;

    /**
     * the running status of this process
     *
     * @var bool
     */
    protected $runningStatus = 0;

    /**
     * if run this process as a daemon
     *
     * @var int
     */
    protected $daemonStatus = 0;

    /**
     * the priority of the process
     * usually, you can just ajust the priority to a lower level
     *
     * @var int
     */
    protected $priority = 0;

    /**
     * the signal handlers of this process
     * you can redefine the behivor of some signal
     *
     * @var array
     */
    protected $signalHandlers = [];

    /**
     * the frequency of the signal dispatch
     * the measuring unit is milliseconds
     *
     * @var int
     */
    protected $signalDispatchfrequency = 200;

    /**
     * the callbacks executed before process task of current process
     *
     * @var array
     */
    protected $beforeProcessCallBack = [];

    /**
     * the callbacks executed after process task of current process
     *
     * @var array
     */
    protected $afterProcessCallBack = [];

    /**
     * Process constructor.
     *
     * @param mixed $callBack
     */
    public function __construct($callBack = null)
    {
        $this->setProcessTask($callBack);
    }

    /**
     * set the task for this process
     *
     * @param mixed $callBack
     *
     * @return $this
     */
    public function setProcessTask($callBack = null)
    {
        if ( !empty( $callBack ) && is_callable($callBack)) {
            $this->task = $callBack;
        }
        elseif ( !empty( $callBack )) {
            throw new \InvalidArgumentException('the callback for the process task is invaliad');
        }

        return $this;
    }

    /**
     * set the process running background as a daemon
     */
    public function asDaemon()
    {
        $this->daemonStatus = 1;
    }

    /**
     * get the priority of the current child process
     *
     * @return int
     */
    public function getPriority()
    {
        return pcntl_getpriority($this->getPid());
    }

    /**
     * get the process id of the process
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * set pid of current process
     *
     * @param int $pid process id
     */
    protected function setPid($pid)
    {
        if (is_numeric($pid) && !empty( $pid )) {
            $this->pid = $pid;
        }
    }

    /**
     * get the task of this process
     *
     * @return mixed
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * get the running status of the process
     *
     * @return bool
     */
    public function getRunningStatus()
    {
        return $this->runningStatus;
    }

    /**
     * set the running status of this process
     *
     * @param int $running
     *
     * @return $this
     */
    protected function setRunningStatus($running = 1)
    {
        if (is_numeric($running)) {
            $this->runningStatus = $running;
        }

        return $this;
    }

    /**
     * update the running status of the process
     *
     * @param int $pid    process id
     * @param int $status process status
     *
     * @return $this
     */
    protected function updateRunningStatus($pid, $status = 1)
    {
        if ( !empty( $pid ) && is_numeric($pid) && is_numeric($status)) {
            $this->setPid($pid);
            $this->setRunningStatus($status);
        }

        return $this;
    }

    /**
     * register  process signal handler
     * note that ,the signal handlers is effective just when the 'start' method is called
     *
     * @param int      $signal  signal such as SIGUSR2.
     * @param callable $handler callback function
     */
    public function registerSignalHandler($signal, callable $handler)
    {
        if ( !empty( $signal ) && is_callable($handler)) {
            $this->signalHandlers[$signal] = $handler;
        }
    }


    /**
     * start this process
     * when started, the system will fork a process
     *
     * @return int if the current process is parent, return the process id of the forked process
     */
    public function start()
    {
        $return = 0;
        //if this process has got a pid and has running,then return false
        if ( !empty( $this->getPid() ) && ( $this->getRunningStatus() === 0 )) {
            return $return;
        }
        //start the process
        if (empty( $this->getPid() ) && ( $this->getRunningStatus() === 0 )) {
            //check the callback function of the process
            $callback = $this->getTask();
            if ( !is_callable($callback)) {
                throw new \RuntimeException('the task of this process is invalid');
            }
            //fork error
            $pid = pcntl_fork();
            if ($pid < 0) {
                throw new \RuntimeException("fork error");
            }
            //children process
            elseif ($pid == 0) {
                $this->runCurrentProcess();
            }
            //current parent process
            else {
                $return = $pid;
            }
        }

        return $return;
    }

    /**
     * run current process
     * set running status、set signal handlers、execure callback task
     */
    protected function runCurrentProcess()
    {
        $pid = getmypid();
        //update the process status as running already
        $this->updateRunningStatus($pid, 1);
        //enable signal handlers if setted
        $this->enableSignalHandlers();
        //execure the callback before this process task
        $this->executeBeforeProcessCallBack();
        //execure the callback task
        $this->executeTask();
        //execure the callback after this process task
        $this->executeBeforeProcessCallBack();
        //exit the process
        exit( 0 );
    }

    /**
     * execure the callback task
     *
     * @return $this
     */
    protected function executeTask()
    {
        //execure the callback task
        call_user_func($this->getTask());

        return $this;
    }

    /**
     * execure the callback before current process task
     *
     * @return $this
     */
    protected function executeBeforeProcessCallBack()
    {
        $beforeCallBack = $this->beforeProcessCallBack;
        if ( !empty( $beforeCallBack )) {
            foreach ($beforeCallBack as $callback) {
                call_user_func($callback);
            }
        }

        return $this;
    }

    /**
     * execure the callback after current process task
     *
     * @return $this
     */
    protected function executeAfterProcessCallBack()
    {
        $afterCallBack = $this->afterProcessCallBack;
        if ( !empty( $afterCallBack )) {
            foreach ($afterCallBack as $callback) {
                call_user_func($callback);
            }
        }

        return $this;
    }

    /**
     * enable signal handlers that you setted before
     *
     * @return $this
     */
    protected function enableSignalHandlers()
    {
        if ( !is_array($this->signalHandlers) && ( count($this->signalHandlers) > 0 )) {
            //enable signal handler
            foreach ($this->signalHandlers as $signal => $handler) {
                pcntl_signal($signal, $handler);
            }
            //lisnten signals
            $this->listenSignals();
        }

        return $this;
    }

    /**
     * listen signals sended to this process, when received , the signal handler will then handle it
     * we do not use declare(ticks = 1) to check signals because it is very inefficient and it is not unnessary
     */
    protected function listenSignals()
    {
        while (true) {
            usleep($this->signalDispatchfrequency);
            pcntl_signal_dispatch();

        }
    }

}