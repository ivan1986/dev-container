<?php

namespace Ivan1986\DevContainer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends Command
{
    protected function configure()
    {
        $this->setName('up')
            ->setDescription('Create or start container');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo 1;
    }

}
