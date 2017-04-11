<?php

namespace Leaf\Managers\ProcessManager\Base;

use Leaf\Managers\ProcessManager\SignalHandler;

/**
 * Class Process
 */
class ProcessBase
{
    
    //进程编号
    private $pid = null;
    //进程的执行回调任务
    private $task = null;
    //进程状态: 0未开始执行, 1正在执行, 2执行完毕
    private $runningStatus = 0;
    //进程的退出码
    private $exitCode = 0;
    //进程优先级
    private $priority = 0;
    //信号处理器
    private $signalHandlers = [];
    //信号集合
    private $signals = [
        SIG_DFL,
        SIG_ERR,
        SIGHUP,
        SIGINT,
        SIGQUIT,
        SIGILL,
        SIGTRAP,
        SIGABRT,
        SIGIOT,
        SIGBUS,
        SIGFPE,
        SIGKILL,
        SIGUSR1,
        SIGSEGV,
        SIGUSR2,
        SIGPIPE,
        SIGALRM,
        SIGTERM,
        SIGCHLD,
        SIGCONT,
        SIGSTOP,
        SIGTSTP,
        SIGTTIN,
        SIGTTOU,
        SIGURG,
        SIGXCPU,
        SIGXFSZ,
        SIGVTALRM,
        SIGPROF,
        SIGWINCH,
        SIGIO,
        SIGSYS,
        SIGBABY,
    ];
    
    /**
     * 构造方法
     * Process constructor.
     */
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * 进程初始化
     */
    public function init()
    {
        //操作系统检测
        if ( !$this->checkIfPosixSys()) {
            throw new \Exception("pcntl is not supported on windows");
        }
    }
    
    /**
     * 检测是否为windows操作系统
     */
    private function checkIfPosixSys()
    {
        return (PHP_OS != 'Windows') ? true : false;
    }
    
    /**
     * 注册信号处理器
     *
     * @param int    $signal  信号
     * @param object $handler 信号处理器,
     *
     * @return $this
     * @throws \Exception
     */
    public function registerSignalHandler($signal, $handler)
    {
        $signal = intval($signal);
        if (in_array($signal, $this->signals) && is_object($handler) && ($handler instanceof SignalHandler)) {
            $this->signalHandlers[$signal] = $handler;
        } else {
            throw new \Exception("注册信号失败, 不允许的信号或信号处理器不可回调");
        }
        
        return $this;
    }
    
    /**
     * 取消某个已经注册的信号处理器
     * 注：此方法只能在子进程未启动前有效
     *
     * @param $signal
     *
     * @return $this
     */
    public function unregisterSignalHandler($signal)
    {
        $signal = intval($signal);
        if (in_array($signal, $this->signals) && isset($this->signalHandlers[$signal])) {
            unset($this->signalHandlers[$signal]);
        }
        
        return $this;
    }
    
    /**
     * 设置进程的优先级
     *
     * @param int $priority 取值范围为 -20<$priority<20
     *
     * @return $this
     */
    public function setPriority($priority = 0)
    {
        $priority = intval($priority);
        if (($priority < -20) || ($priority > 20)) {
            $priority = 0;
        }
        $this->priority = $priority;
        
        return $this;
    }
    
    /**
     * 设置进程的优先级
     * 注: 此方法, 会在fork之后, 由子进程调用
     * renice比PHP原生的pcntl_setpriority要有用
     *
     * @return bool
     */
    private function setPriorityInner()
    {
        $return = false;
        if ($this->priority !== 0) {
            $pid = getmypid();
            
            $return = system("renice  {$this->priority} -p $pid") != false;
        }
        
        return $return;
    }
    
    /**
     * 设置进程的pid
     *
     * @param null $pid
     *
     * @return $this
     */
    private function setPid($pid = null)
    {
        if (is_null($this->pid) && ($pid > 0)) {
            $this->pid = $pid;
        }
        
        return $this;
    }
    
    /**
     * 获取进程的pid
     *
     * @return null|int
     */
    public function getPid()
    {
        return $this->pid;
    }
    
    /**
     * 设置进程的执行任务
     *
     * @param callable|null $task
     *
     * @return $this
     */
    public function setTask($task = null)
    {
        if (is_null($this->task) && is_callable($task)) {
            $this->task = $task;
        }
        
        return $this;
    }
    
    /**
     * 等待子进程执行完毕, 并收集子进程的执行状态
     *
     * @return array 返回结果中,是进程id对应的执行完成状态信息
     */
    public function wait()
    {
        $return = [];
        $status = null;
        //pid<0：子进程都没了
        //pid>0：捕获到一个子进程退出的情况
        //pid=0：没有捕获到退出的子进程
        while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) >= 0) {
            if ($pid > 0) {
                $this->updateStatus(2);
            } elseif ($pid == 0) {
                //休眠 50 毫秒，防止因等待子进程状态造成无畏的循环资源
                usleep(50);
            }
        }
        
        return $return;
    }
    
    /**
     * 检测当前进程是否已执行完毕
     *
     * @return int
     */
    public function isFinished()
    {
        return ($this->runningStatus == 2) ? true : false;
    }
    
    /**
     * 检测是否正在执行
     *
     * @return int
     */
    public function isStarted()
    {
        return ($this->runningStatus == 1) ? true : false;
    }
    
    /**
     * 获取进程的运行状态
     * 这个函数很核心，它其实不仅仅是获取状态，它还会在获取的时候，尝试更新进程状态
     *
     * @return int
     */
    public function getProcessStatus()
    {
        //对于还没开始执行进行或者已经执行结束的进程，直接返回即可
        if ($this->runningStatus == 1) {
            //pid<0：子进程都没了
            //pid>0：捕获到一个子进程退出的情况
            //pid=0：没有捕获到退出的子进程
            $pid = pcntl_waitpid($this->getPid(), $status, WNOHANG);
            if ($pid > 0 || $pid == -1) {
                $this->updateStatus(2);
            }
        }
        
        return $this->runningStatus;
    }
    
    /**
     * 更新当前进程的状态
     * 0未开始执行, 1正在执行任务, 2进程执行完毕
     *
     * @param int $status
     *
     * @return $this
     */
    private function updateStatus($status = 0)
    {
        if (in_array($status, [0, 1, 2])) {
            $this->runningStatus = $status;
        }
        
        return $this;
    }
    
    /**
     * 执行任务
     *
     * @param int $daemon 是否已守护进程的方式运行
     *
     * @return $this
     * @throws \Exception
     */
    public function run($daemon = 0)
    {
        //如果已经运行或运行结束了，执行返回
        if ($this->isStarted() || $this->isFinished()) {
            return $this;
        }
        //检测进程任务是否可回调
        if ( !is_callable($this->task)) {
            throw new \Exception("execute error: invaliad process task!");
        }
        $pid = pcntl_fork();
        //fork失败
        if ($pid < 0) {
            throw new \Exception('fork child process error!');
        } //父进程
        elseif ($pid > 0) {
            $this->setPid($pid);
            $this->updateStatus(1);
        } //子进程
        elseif ($pid === 0) {
            //是否开启守护进程模式
            if ($daemon) {
                $this->daemon();
            }
            //设置进程优先级
            $this->setPriorityInner();
            //执行注册的信号处理器
            $this->loopSignalHandlers();
            if ( !is_null($this->task)) {
                $this->callUserFunc();
            }
            exit(0);
        }
        
        return $this;
    }
    
    protected function callUserFunc()
    {
        call_user_func($this->task);
    }
    
    public function getTask()
    {
        return $this->task;
    }
    
    /**
     * 在子进程中,执行之前设置好的信号处理器
     * 注意：只能注册用户级别的信号，比如SIGKILL是不允许的
     * http://php.net/manual/en/function.pcntl-signal.php ：You cannot assign a signal handler for SIGKILL (kill -9).
     *
     * @return $this
     */
    private function loopSignalHandlers()
    {
        if ( !empty($this->signalHandlers)) {
            foreach ($this->signalHandlers as $signNo => $handler) {
                if ($handler instanceof SignalHandler) {
                    pcntl_signal($signNo, $handler->handler, false);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * 暂停进程的执行
     */
    public function pause()
    {
    
    }
    
    /**
     * 杀掉正在执行的进程
     *
     * @return bool
     */
    public function kill()
    {
        return posix_kill($this->pid, SIGKILL);
    }
    
    /**
     * 发送一个
     *
     * @param $signal
     *
     * @return bool
     */
    public function sendSignal($signal)
    {
        return posix_kill($this->pid, $signal);
    }
    
    /**
     * 设置进程以守护进程的方式存在
     * 守护进程最主要的特点是，终端退出的时候，进程依然可执行
     *
     * @return $this
     * @throws \Exception
     */
    private function daemon()
    {
        // reopen standard file descriptors
        // this is necessary to decouple the daemon from the TTY
        fclose(STDIN);
        //fclose(STDOUT);
        //fclose(STDERR);
        //启用新回话运行当前进程
        $sid = posix_setsid();
        if ($sid < 0) {
            throw new \Exception('error occurs when making process daemons!');
        }
        //设置忽略SIGHUP信号（退出终端时发送的SIGHUP信号(kill -SIGHUP PID)会被忽略掉）
        $this->registerSignalHandler(SIGHUP, new IgnoreSighup());
        
        return $this;
    }
    
    /**
     * 设置进程被杀掉后，执行的最后任务
     * 当主进程，杀死子进程的时候，有时候我们希望子进程能在接收到被杀信号后，还能处理一些自定义的任务，此方法目的就是注册这个最后的任务
     * 注意：
     * 1、此方法，此方法依赖于posix信号，所以，你的代码入口，必须声明 declare(ticks=1)，doc：http://php.net/manual/en/function.pcntl-signal.php
     * 2、该方法注册的是SIGUSR2信号，如果使用者再次注册了同样信号，则原来的会被覆盖
     *
     * 函数本质，是注册了SIGUSR2信号，因为操作系统不允许注册 SIGKILL 信号。
     * 此方法，必须在 run 之前使用，因为run方法就已经开始fork进程了
     *
     * @param $handler
     *
     * @return $this
     */
    public function addBeKilledHandler($handler)
    {
        $this->registerSignalHandler(SIGUSR2, $handler);
        
        return $this;
    }
    
    /**
     * 判断当前进程，是否有可执行的任务
     *
     * @return bool
     */
    public function taskCheck()
    {
        return !is_null($this->task) ? true : false;
    }
    
}