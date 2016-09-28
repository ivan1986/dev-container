<?php

namespace Ivan1986\DevContainer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnsibleCommand extends Command
{
    protected function configure()
    {
        $this->setName('ansible')
            ->setDescription('Run Ansible in container');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->getStorage()->ansible();
    }

}
