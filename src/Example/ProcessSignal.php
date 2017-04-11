<?php

//声明使用信号（必须声明，否则子进程无法捕获信号）
declare(ticks=1);

/**
 * 示例：启动一个进程，并执行某个耗时任务，在子进程还没有执行完的时候，主进程向其发送一个自定义的终止信号，子进程接收到信号后，输出一句话并退出
 */

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Managers\ProcessManager\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Managers\ProcessManager\Process;
use \Leaf\Managers\ProcessManager\SignalHandler;

class exitHandler extends SignalHandler
{
    public function handleSignal($signo)
    {
        echo "我是子进程，已收到结束信号，我现在退出了..." . PHP_EOL;
        exit(0);
    }
}

$exitHandler = new exitHandler();

/**
 * 子进程工作100s
 */
function task()
{
    echo 'task begin' . PHP_EOL;
    $i = 0;
    while ($i < 100) {
        echo '子进程运行：' . $i . PHP_EOL;
        sleep(1);
        $i++;
    }
    echo 'task end' . PHP_EOL;
}

$process = new Process();
//task任务必须是可回调的函数，callback类型
$process->setTask('task');
$process->registerSignalHandler(SIGUSR2, $exitHandler);    //先注册好信号
$process->run();
sleep(5);   //让运行5s后，给子进程发送信号
$process->sendSignal(SIGUSR2);
