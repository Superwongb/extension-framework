<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Harryn\Jacobn\ExtensionFrameworkBundle\DependencyInjection\ContainerExtension;
use Harryn\Jacobn\ExtensionFrameworkBundle\DependencyInjection\Passes\RoutingPass;
use Harryn\Jacobn\ExtensionFrameworkBundle\DependencyInjection\Passes\ConfigurationPass;
use Harryn\Jacobn\ExtensionFrameworkBundle\DependencyInjection\Passes\PackageConfigurationPass;

class UVDeskExtensionFrameworkBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ContainerExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new RoutingPass())
            ->addCompilerPass(new ConfigurationPass())
            ->addCompilerPass(new PackageConfigurationPass());
    }
}
