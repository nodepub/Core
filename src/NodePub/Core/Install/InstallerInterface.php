<?php

namespace NodePub\Core\Install;

interface InstallerInterface
{
    /**
     * @returns array
     */
    public function getEntityClasses();

    /**
     * @returns bool
     */
    public function install();
}