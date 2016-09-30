<?php

namespace Ivan1986\DevContainer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DestroyCommand extends Command
{
    protected function configure()
    {
        $this->setName('destroy')
            ->setDescription('Destroy container');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->getStorage()->destroy();
    }

}
