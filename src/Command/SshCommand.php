<?php

namespace Ivan1986\DevContainer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SshCommand extends Command
{
    protected function configure()
    {
        $this->setName('ssh')
            ->setDescription('Ssh to container');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->getStorage()->ssh();
    }

}
