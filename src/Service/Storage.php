<?php

namespace Ivan1986\DevContainer\Service;

use Docker\API\Model\ContainerConfig;
use Docker\API\Model\HostConfig;
use Docker\Docker;
use Eloquent\Composer\Configuration\Element\Configuration as ComposerConfiguration;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\ServerErrorException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
        $container = null;
        try {
            $container = $this->docker->getContainerManager()->find($this->name);
        } catch (ClientErrorException $e) {
            $container = $this->build();
        }
        echo 'up';

    }

    public function ssh()
    {
        echo 'ssh';

    }

    public function ansible()
    {
        echo 'ansible';
    }

    public function rebuild()
    {
        try {
            $container = $this->docker->getContainerManager()->remove($this->name);
        } catch (ClientErrorException $e) {
        }
        $this->build();
    }

    protected function build()
    {
        $config = new ContainerConfig();
        $config
            ->setImage(self::IMAGE)
            ->setVolumes([
                '/srv/web/' . $this->name => new \ArrayObject(),
            ])
            ->setHostConfig(
                (new HostConfig())
                    ->setBinds([ PROJECT_DIR . ':/srv/web/' . $this->name ])
            )
        ;
        try {
            $this->docker->getContainerManager()->create($config, ['name' => $this->name]);
        } catch (ServerErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '.$e->getResponse()->getBody();
        } catch (ClientErrorException $e) {
            echo $e->getResponse()->getStatusCode() .' '. $e->getResponse()->getBody();
        }
        echo 'build';
    }
}
