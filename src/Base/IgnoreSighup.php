<?php

namespace Leaf\Managers\ProcessManager\Base;

use Leaf\Managers\ProcessManager\SignalHandler;

class IgnoreSighup extends SignalHandler
{
    public function handleSignal($signo)
    {
        parent::handleSignal($signo);
    }
}