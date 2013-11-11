<?php

namespace NodePub\Core\Provider;

use Silex\Application;

/**
 * Prepends a base uri segment to a route uri.
 * Allows configuring a base admin route that can
 * be prepended to all admin routes.
 */
class RoutePrefixFactory
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
