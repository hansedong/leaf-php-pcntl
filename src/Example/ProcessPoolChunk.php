<?php

/**
 * 分块处理任务方式的进程池
 * 【固定进程数量】的进程池，将$chunkData传递给池，进程池自己根据池的大小（poolSize），每个进程处理的任务大小（dataSize）来自动分配给进程一块数据处理。
 * 一共20个进程，进程池大小为2
 * 引用场景：
 * ①：你有1000个元素，单进程处理较慢，你想多进程，每个进程处理一部分。此方法适用于数据量不是而别大的多进程方式。如果你有1000万条数据，这个就不合适了
 * 因为将1000万个数据分组，就会非常消耗内存。如果真要这么处理，可以考虑结合PHP的协程，看是否可以满足。
 * ②：每个子进程执行的方法相同，只是参数不同而已。
 * 弊端：$chunkData如果数据量很大，进程池内存分块的时候，可能会占用较高内存。
 */

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Managers\ProcessManager\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Managers\ProcessManager\ProcessPoolManagerChunk;

//进程池方式, 每个进程处理一个渠道
$processPool = new ProcessPoolManagerChunk();
//需要分块处理的数据
$chunkData = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

function task($params = [], $taskId = 0)
{
    //你的业务逻辑在这里
    var_dump([$params, $taskId]);
    sleep(10);
}

/**
 * $chunkData数据为10个元素的数组，setPoolSize第一个参数为并行进程数，第二个参数为每个进程分配的数据大小。
 * 执行过程如：
 * 因为每个进程处理的任务为2，所以，实际运行过程层中，进程池会将$chunkData每2个元素分为一组，一共分了5组，所以，一共会启动5个进程，也就是每个进程处理2个元素
 * 我们可以执行此示例，观测效果
 */

echo "多进程处理开始" . PHP_EOL;
$return = $processPool->setChunkData($chunkData)->setCallBackParams([])->setPoolSize(2, 2)->addPoolTask('task')->execute();
echo "多进程处理结束" . PHP_EOL;
return $return;