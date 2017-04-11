<?php

/**
 * 固定进程数量的进程池
 * 一共20个进程，进程池大小为2
 * 使用者可通过：ps -ef|grep FixProcessPoll 来看看进程状态
 */

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Managers\ProcessManager\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Managers\ProcessManager\Process;
use \Leaf\Managers\ProcessManager\ProcessPool;

//初始化进程池
$pool = new ProcessPool();
$pool->setPoolSize(2);

$time = time();

//启动100个进程，以进程池的方式运行，进程池大小为10
for ($i = 0; $i < 10; $i++) {
    $process = new Process();
    //task任务必须是可回调的函数，callback类型
    $process->setTask('task');
    usleep(200);
    $pool->addProcess($process);
}

$pool->execute();

echo "到此，主进程留下，所有子进程应该退出了，10s后我也会退出" . PHP_EOL;
sleep(10);
echo "到此，主进程退出" . PHP_EOL;

function task()
{
    global $time;
    $diff = time() - $time;
    if ($diff < 2) {
        $diff = 2;
    }
    //为了避免20个进程，一同开启一同结束观测不到效果，sleep时间
    sleep($diff);
    echo "task：我休眠：" . $diff . '秒' . PHP_EOL;
}