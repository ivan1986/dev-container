<?php

namespace Ivan1986\DevContainer\Containers;

use Docker\API\Model\ContainerConfig;
use Docker\API\Model\ExecConfig;
use Docker\API\Model\ExecStartConfig;
use Docker\API\Model\HostConfig;
use Docker\Docker as DockerApi;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\ServerErrorException;

class Docker implements Container
{
    const IMAGE = 'ivan1986/dev-container';

    /**
     * @var \Docker\Docker
     */
    private $docker;

    protected $name;

    /**
     * Docker constructor.
     */
    public function __construct()
    {
        $this->docker = new DockerApi();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function exist()
    {
        try {
            $this->docker->getContainerManager()->find($this->name);
        } catch (ServerErrorException $e) {
            return false;
        } catch (ClientErrorException $e) {
            return false;
        }

        return true;
    }

    public function getIP()
    {
        try {
            $containerInfo = $this->docker->getContainerManager()->find($this->name);
        } catch (ServerErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        } catch (ClientErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        }

        return $containerInfo->getNetworkSettings()->getIPAddress();
    }

    public function getSSH()
    {
        return 'web@'.$this->getName().'.docker';
    }

    public function build()
    {
        $mapping = [
            '/sys/fs/cgroup:/sys/fs/cgroup:ro',
            PROJECT_DIR.':/srv/web/'.$this->name,
        ];

        // TODO: dev mapping - remove after release
        if (is_dir(PROJECT_DIR.'/../DevContainer')) {
            $mapping[] = realpath(PROJECT_DIR.'/../DevContainer').':/srv/web/'.'DevContainer';
        }

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
                    ->setBinds($mapping)
            )
        ;
        try {
            $this->docker->getContainerManager()->create($config, ['name' => $this->name]);
        } catch (ServerErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        } catch (ClientErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        }
    }

    public function start()
    {
        $this->docker->getContainerManager()->start($this->name);
    }

    public function destroy()
    {
        try {
            $this->docker->getContainerManager()->remove($this->name, ['force' => 1]);
        } catch (ServerErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        } catch (ClientErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        }
    }

    public function exec($command)
    {
        try {
            $exec = $this->docker->getExecManager()->create($this->name,
                (new ExecConfig())
                    ->setAttachStderr(true)
                    ->setAttachStdout(true)
                    ->setTty(true)
                    ->setCmd(['sh', '-c', $command])
            );

            $this->docker->getExecManager()->start($exec->getId(),
                (new ExecStartConfig())
                    ->setTty(true)
            );
        } catch (ServerErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        } catch (ClientErrorException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode());
        }
    }
}
