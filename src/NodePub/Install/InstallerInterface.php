<?php

namespace NodePub\Install;

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