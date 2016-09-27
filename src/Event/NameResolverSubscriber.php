<?php

namespace Ivan1986\DevContainer\Event;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NameResolverSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        var_dump($event->getInput()->getOption('name'));
    }

}
