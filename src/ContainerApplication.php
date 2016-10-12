<?php

namespace Ivan1986\DevContainer;

use Ivan1986\DevContainer\Service\Storage;
use Symfony\Component\Console\Application;

class ContainerApplication extends Application
{
    /**
     * @var Storage
     */
    protected $storage;

    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}
