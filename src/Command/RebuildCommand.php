<?php

namespace Ivan1986\DevContainer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildCommand extends Command
{
    protected function configure()
    {
        $this->setName('rebuild')
            ->setDescription('Rebuild container');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->getStorage()->rebuild();
    }

}
