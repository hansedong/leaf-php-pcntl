<?php

/**
 * 偏移量处理任务方式的进程池
 * 【固定进程数量】的进程池，将一个起始值和结束值，传递给池，进程池自己根据池的大小（poolSize），每个进程处理的任务大小（dataSize）来自动分配一个偏移量给具体的
 * 执行进程。
 * 一共10万个数据，进程池大小为50，实际上会启动2000个进程。每个进程拿到的offset分别是 0-50，51-101，102-152，...
 * 适用场景：
 * 比如你有1000万条评论数据要处理，那么 ProcessPoolChunk 的方式，很耗费内存，因为你要先把这1000万条数据的id取出来，Chunk的方式再分组，内存压力很大。而使用这种
 * 方式，你只需要拿到这1000万数据的id最小值和最大值即可。Offset的方式，会传递评论id范围传递给具体的进程上。每个进程只需要处理自己负责的部分就可以了。
 * 弊端：如果数据量很大，每个进程处理的任务数量小，仍然需要创建大量进程对象，内存消耗依然不乐观。
 *
 */

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Managers\ProcessManager\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Managers\ProcessManager\ProcessPoolManagerSocketPair;

//进程池方式, 每个进程处理一个渠道
$processPool = new ProcessPoolManagerSocketPair();

/**
 * 子进程的处理任务，$data为父进程投递的字符串数据
 *
 * @param string $data
 */
function task($data = '')
{
    //你的业务逻辑在这里
    echo "im worker " . getmypid() . "，I receive data：" . $data . PHP_EOL;
}

echo "多进程处理开始" . PHP_EOL;
$return = $processPool->setPoolSize(10)->addPoolTask('task')->setPoolData(["aa", "bb", "ccc", "ddd"])->execute();
echo "多进程处理结束" . PHP_EOL;
return $return;