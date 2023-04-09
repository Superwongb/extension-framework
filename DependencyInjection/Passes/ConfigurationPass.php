<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\DependencyInjection\Passes;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\MappingResource;
use Harryn\Jacobn\ExtensionFrameworkBundle\Configurators\Configurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Package\PackageInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Application\ApplicationInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Utils\PackageCollection;
use Harryn\Jacobn\ExtensionFrameworkBundle\Utils\ApplicationCollection;

class ConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has(MappingResource::class)) {
            $configurator = new Reference(Configurator::class);
            $mappingResource = $container->findDefinition(MappingResource::class);

            foreach ($container->findTaggedServiceIds(PackageInterface::class) as $reference => $tags) {
                $mappingResource->addMethodCall('setPackage', array($reference, $tags));

                $packageDefinition = $container->findDefinition($reference);
                $packageDefinition->setConfigurator([$configurator, 'configurePackage']);
            }

            foreach ($container->findTaggedServiceIds(ApplicationInterface::class) as $reference => $tags) {
                $mappingResource->addMethodCall('setApplication', array($reference, $tags));

                $applicationDefinition = $container->findDefinition($reference);
                $applicationDefinition->setConfigurator([$configurator, 'configureApplication']);
            }

            $container->findDefinition(Configurator::class)->addMethodCall('autoconfigure');
        }

        if ($container->has(PackageCollection::class)) {
            $packageCollectionDefinition = $container->findDefinition(PackageCollection::class);
            $packageCollectionDefinition->setLazy(true)->addMethodCall('organizeCollection');
        }

        if ($container->has(ApplicationCollection::class)) {
            $packageCollectionDefinition = $container->findDefinition(ApplicationCollection::class);
            $packageCollectionDefinition->setLazy(true)->addMethodCall('organizeCollection')->setLazy(true);
        }
    }
}
