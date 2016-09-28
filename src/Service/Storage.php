<?php

namespace Ivan1986\DevContainer\Service;

use Docker\Docker;
use Docker\DockerClient;
use Eloquent\Composer\Configuration\Element\Configuration as ComposerConfiguration;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
        echo 'rebuild';
    }
}
