<?php

namespace Ivan1986\DevContainer\Service;

use Eloquent\Composer\Configuration\Element\Configuration as ComposerConfiguration;
use Ivan1986\DevContainer\Containers\Container;
use Ivan1986\DevContainer\Containers\Docker;
use Ivan1986\DevContainer\Containers\Vagrant;
use stdClass;
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
     * @var array
     */
    protected $resolvers = [];

    /**
     * @var Container
     */
    private $container;

    public function __construct(ComposerConfiguration $composer)
    {
        $this->composer = $composer;

        $containerType = $this->getComposerExtra('container', PHP_OS == 'Linux' ? 'docker' : 'vagrant');

        $this->container = $containerType == 'docker' ? new Docker() : new Vagrant();

        $this->resolvers = $this->getComposerExtra('resolvers', []);
        $resolver = $this->getComposerExtra('resolver', false);
        if ($resolver) {
            $this->resolvers[] = $resolver;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->container->setName(
            $event->getInput()->getOption('name') ?: $this->composer->projectName()
        );
    }

    public function up()
    {
        if (!is_file(PROJECT_DIR.'/dev-container/playbook.yml')) {
            echo <<<RUN
run
vendor/bin/container init
for init

RUN;
            return;
        }

        $firstRun = false;
        if (!$this->container->exist()) {
            $this->container->build();
            $firstRun = true;
        }

        $this->container->start();

        if ($firstRun) {
            $this->init();
        }

        if (!empty($this->resolvers)) {
            foreach ($this->resolvers as $resolver) {
                echo $resolver.' set ';
                (new Process('echo "nameserver '.$this->container->getIP().'" | sudo tee /etc/resolver/'.$resolver))
                    ->setTty(true)
                    ->run();
            }
        }

        $this->info();
    }

    public function ansible()
    {
        (new Process('ssh web@'.$this->container->getIP().' ./init.sh '.$this->container->getName()))
            ->setTty(true)
            ->run();
    }

    public function rebuild()
    {
        $this->destroy();
        $this->container->build();
        $this->up();
        $this->init();
        $this->info();
    }

    public function destroy()
    {
        $this->container->destroy();
    }

    protected function init()
    {
        $this->copySshKey();
        $this->container->exec('cp '.'/srv/web/'.$this->container->getName().'/vendor/ivan1986/dev-container/ansible/init.sh /srv/web/init.sh');
        $this->container->exec('sed \'s$#path#$/srv/web/'.$this->container->getName().'$g\' /srv/web/'.$this->container->getName().'/vendor/ivan1986/dev-container/ansible/ansible.cfg > /srv/web/.ansible.cfg');
        do {
            $p = new Process('ssh web@'.$this->container->getIP().' ls');
            $p->run();
        } while ($p->getExitCode());
        $this->ansible();
    }

    protected function info()
    {
        echo <<<RUN
Container start on {$this->container->getIP()}
to login type:
ssh {$this->container->getSSH()}

RUN;
    }

    protected function copySshKey()
    {
        $key = file_get_contents(getenv('HOME').'/.ssh/id_rsa.pub');
        $this->container->exec('mkdir /srv/web/.ssh');
        $this->container->exec('echo \''.$key.'\' > /srv/web/.ssh/authorized_keys');
        $this->container->exec('chmod -R 600 /srv/web/.ssh/authorized_keys');
        $this->container->exec('chown -R web:web /srv/web/.ssh');
    }

    protected function getComposerExtra($name, $default = null)
    {
        $extra = $this->composer->extra();
        if (!$extra || !property_exists($extra, 'dev-container')) {
            return $default;
        }
        $extra = $extra->{'dev-container'};

        return property_exists($extra, $name) ? $extra->{$name} : $default;
    }
}
