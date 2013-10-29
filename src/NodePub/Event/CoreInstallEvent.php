<?php

namespace NodePub\Event;

use Symfony\Component\EventDispatcher\Event;
use NodePub\Core\Installer;

class CoreInstallEvent extends Event
{
    protected $installer;

    public function __construct(Installer $installer)
    {
        $this->installer = $installer;
    }

    public function getInstaller()
    {
        return $this->installer;
    }
}