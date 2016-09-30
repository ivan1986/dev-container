<?php

namespace Ivan1986\DevContainer\Service;

use Docker\API\Model\ContainerConfig;
use Docker\API\Model\ExecConfig;
use Docker\API\Model\ExecStartConfig;
use Docker\API\Model\HostConfig;
use Docker\Docker;
use Eloquent\Composer\Configuration\Element\Configuration as ComposerConfiguration;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\ServerErrorException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

class Storage implements EventSubscriberInterface
{
    const IMAGE = 'ivan1986/dev-container';

    /**
     * @var ComposerConfiguration
     */
    protected $composer;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Docker\Docker
     */
    private $docker;

    public function __construct(ComposerConfiguration $composer, Docker $docker)
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
        $this->name = $event->getInput()->getOption('name') ?: $this->composer->projectName();
    }

    public function up()
    {
        $firstRun = false;
        try {
            $container = $this->docker->getContainerManager()->find($this->name);
        } catch (ClientErrorException $e) {
            $this->build();
            $firstRun = true;
            $container = $this->docker->getContainerManager()->find($this->name);
        }

        $this->docker->getContainerManager()->start($this->name);

        if ($firstRun) {
            $this->init();
        }

        echo <<<RUN
Container start on {$this->getContainerIp()}
to login type:
ssh web@{$this->getContainerIp()}
or
ssh web@{$this->name}.docker

RUN;
    }

    public function ansible()
    {
        $p = new Process('ssh web@'.$this->getContainerIp().' ansible-playbook '.$this->name.'/vendor/ivan1986/dev-container/ansible/playbook.yml');
        $p->setTty(true);
        $p->run();
    }

    public function rebuild()
    {
        $this->destroy();
        $this->build();
        $this->up();
        $this->init();
    }

    public function destroy()
    {
        try {
            $container = $this->docker->getContainerManager()->remove($this->name, ['force' => 1]);
        } catch (ClientErrorException $e) {
        }
    }

    protected function init()
    {
        $this->copySshKey();
        $this->exec('cp '.'/srv/web/'.$this->name.'/vendor/ivan1986/dev-container/ansible/ansible.cfg /srv/web/.ansible.cfg');
        do {
            $p = new Process('ssh web@' . $this->getContainerIp() . ' ls');
            $p->run();
        } while ($p->getExitCode());
        $this->ansible();
    }

    protected function getContainerIp()
    {
        try {
            $containerInfo = $this->docker->getContainerManager()->find($this->name);
        } catch (ClientErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '. $e->getResponse()->getBody();
        }

        return $containerInfo->getNetworkSettings()->getIPAddress();
    }

    protected function exec($command)
    {
        try {
            $exec = $this->docker->getExecManager()->create($this->name,
                (new ExecConfig())
                    ->setAttachStderr(true)
                    ->setAttachStdout(true)
                    ->setTty(true)
                    ->setCmd(['sh', '-c', $command])
            );
        } catch (ServerErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '.$e->getResponse()->getBody();
        } catch (ClientErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '. $e->getResponse()->getBody();
        }

        try {
            $resp = $this->docker->getExecManager()->start($exec->getId(),
                (new ExecStartConfig())
                    ->setTty(true)
            );
        } catch (ServerErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '.$e->getResponse()->getBody();
        } catch (ClientErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '. $e->getResponse()->getBody();
        }
    }

    protected function copySshKey()
    {
        $key = file_get_contents($_SERVER['HOME'].'/.ssh/id_rsa.pub');
        $this->exec('mkdir /srv/web/.ssh');
        $this->exec('echo \''.$key. '\' > /srv/web/.ssh/authorized_keys');
        $this->exec('chmod -R 600 /srv/web/.ssh/authorized_keys');
        $this->exec('chown -R web:web /srv/web/.ssh');
    }

    protected function build()
    {
        $config = new ContainerConfig();
        $config
            ->setImage(self::IMAGE)
            ->setHostname($this->name)
            ->setVolumes([
                '/sys/fs/cgroup' => new \ArrayObject(),
            ])
            ->setHostConfig(
                (new HostConfig())
                    ->setPrivileged(true)
                    ->setBinds([
                        '/sys/fs/cgroup:/sys/fs/cgroup:ro',
                        PROJECT_DIR . ':/srv/web/' . $this->name,
                        '/home/ivan/projects/DevContainer' . ':/srv/web/' . 'DevContainer',
                    ])
            )
        ;
        try {
            $this->docker->getContainerManager()->create($config, ['name' => $this->name]);
        } catch (ServerErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '.$e->getResponse()->getBody();
        } catch (ClientErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '. $e->getResponse()->getBody();
        }
    }
}
