<?php

namespace Leaf\Managers\ProcessManager;

use Leaf\Managers\ProcessManager\Base\ProcessBase;

/**
 *
 * Class ProcessPoolTask
 * ProcessPoolTask定制化了ProcessBase，可以支持给callback传递参数，所以，如果你的进程任务task，需要增加参数传递支持，可以使用此类。
 *
 * @package Leaf\Managers\ProcessManager
 */
class ProcessWithParams extends ProcessBase
{
    
    private $params = [];
    
    /**
     * 重写ProcessBase的callUserFunc方法，这样一来，便可支持给最终执行的callback添加参数传递支持
     */
    public function callUserFunc()
    {
        call_user_func($this->getTask(), $this->params, getmypid());
    }
    
    public function setParams($params = [])
    {
        $this->params = $params;
        
        return $this;
    }
    
}