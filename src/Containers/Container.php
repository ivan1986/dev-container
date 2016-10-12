<?php

namespace Ivan1986\DevContainer\Containers;

interface Container
{
    public function setName($name);
    public function getName();

    public function exist();

    public function getIP();

    public function getSSH();

    public function build();

    public function start();

    public function destroy();

    public function exec($commnad);
}
