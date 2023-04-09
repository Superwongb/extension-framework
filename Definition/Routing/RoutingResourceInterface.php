<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Routing;

interface RoutingResourceInterface
{
    CONST YAML_RESOURCE = 'yaml';

    public static function getResourcePath();
    public static function getResourceType();
}
