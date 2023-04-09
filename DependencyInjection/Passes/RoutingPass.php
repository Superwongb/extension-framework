<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\DependencyInjection\Passes;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\RouteLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Routing\RoutingResourceInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Routing\ApiRoutingResourceInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Routing\ExposedRoutingResourceInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Routing\ProtectedRoutingResourceInterface;

class RoutingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has(RouteLoader::class)) {
            $router = $container->findDefinition(RouteLoader::class);

            foreach ($container->findTaggedServiceIds(RoutingResourceInterface::class) as $id => $tags) {
                $class = new \ReflectionClass($id);

                if ($class->implementsInterface(ExposedRoutingResourceInterface::class)) {
                    $router->addMethodCall('addExposedRoutingResource', array(new Reference($id), $tags));
                } else if ($class->implementsInterface(ProtectedRoutingResourceInterface::class)) {
                    $router->addMethodCall('addProtectedRoutingResource', array(new Reference($id), $tags));
                } else if ($class->implementsInterface(ApiRoutingResourceInterface::class)) {
                    $router->addMethodCall('addApiRoutingResource', array(new Reference($id), $tags));
                }
            }
        }
    }
}
