<?php

namespace Ivan1986\DevContainer\Command;

use Ivan1986\DevContainer\ContainerApplication;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    protected function getStorage()
    {
        $application = $this->getApplication();
        if ($application instanceof ContainerApplication) {
            return $application->getStorage();
        }
    }
}
