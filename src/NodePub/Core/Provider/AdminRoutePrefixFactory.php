<?php

namespace NodePub\Core\Provider;

use Silex\Application;

class AdminRoutePrefixFactory
{
    protected $basePrefix;

    function __construct($basePrefix)
    {
        $this->basePrefix = $basePrefix;
    }

    public function create($prefix)
    {
        if (isset($this->basePrefix)) {
            $prefix = $this->basePrefix . $prefix;
        }

        return $prefix;
    }
}
