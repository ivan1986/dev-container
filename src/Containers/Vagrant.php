<?php

namespace Ivan1986\DevContainer\Containers;

class Vagrant implements Container
{
    protected $name;


    public function __construct()
    {
        die('Not implemented yet');
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
    }

    public function getIP()
    {
    }

    public function build()
    {
    }

    public function start()
    {
    }

    public function destroy()
    {
    }

    public function exec($command)
    {
    }

}
