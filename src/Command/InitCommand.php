<?php

namespace Ivan1986\DevContainer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Init project - first run wizard');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
