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
        $dir = PROJECT_DIR.'/dev-container';

        if (is_file($dir.'/playbook.yml')) {
            $output->writeln("Project was init");
            return;
        }

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        copy(PROJECT_DIR.'/vendor/ivan1986/dev-container/ansible/playbook.yml', $dir.'/playbook.yml');
    }
}
