<?php

namespace Ivan1986\DevContainer\Service;

use Docker\API\Model\ContainerConfig;
use Docker\API\Model\ExecConfig;
use Docker\API\Model\ExecStartConfig;
use Docker\API\Model\HostConfig;
use Eloquent\Composer\Configuration\Element\Configuration as ComposerConfiguration;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\ServerErrorException;
use Ivan1986\DevContainer\Containers\Container;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

class Storage implements EventSubscriberInterface
{
    /**
     * @var ComposerConfiguration
     */
    protected $composer;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Container
     */
    private $docker;

    public function __construct(ComposerConfiguration $composer, Container $docker)
    {
        $this->composer = $composer;
        $this->docker = $docker;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->docker->setName(
            $event->getInput()->getOption('name') ?: $this->composer->projectName()
        );
    }

    public function up()
    {
        $firstRun = false;
        if (!$this->docker->exist()) {
            $this->docker->build();
            $firstRun = true;
        }

        $this->docker->start();

        if ($firstRun) {
            $this->init();
        }

        echo <<<RUN
Container start on {$this->docker->getIP()}
to login type:
ssh web@{$this->docker->getIP()}
or
ssh web@{$this->docker->getName()}.docker

RUN;
    }

    public function ansible()
    {
        (new Process('ssh web@'.$this->docker->getIP().' ./init.sh '.$this->docker->getName()))
            ->setTty(true)
            ->run();
    }

    public function rebuild()
    {
        $this->destroy();
        $this->docker->build();
        $this->up();
        $this->init();
    }

    public function destroy()
    {
        $this->docker->destroy();
    }

    protected function init()
    {
        $this->copySshKey();
        $this->docker->exec('cp '.'/srv/web/'.$this->docker->getName().'/vendor/ivan1986/dev-container/ansible/init.sh /srv/web/init.sh');
        $this->docker->exec('sed s/#name#/'.$this->docker->getName().'/g '.'/srv/web/'.$this->docker->getName().'/vendor/ivan1986/dev-container/ansible/ansible.cfg > /srv/web/.ansible.cfg');
        do {
            $p = new Process('ssh web@' . $this->docker->getIP() . ' ls');
            $p->run();
        } while ($p->getExitCode());
        $this->ansible();
    }


    protected function copySshKey()
    {
        $key = file_get_contents($_SERVER['HOME'].'/.ssh/id_rsa.pub');
        $this->docker->exec('mkdir /srv/web/.ssh');
        $this->docker->exec('echo \''.$key. '\' > /srv/web/.ssh/authorized_keys');
        $this->docker->exec('chmod -R 600 /srv/web/.ssh/authorized_keys');
        $this->docker->exec('chown -R web:web /srv/web/.ssh');
    }

}
