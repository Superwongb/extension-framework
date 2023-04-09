<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\DependencyInjection;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\MappingResource;
use Harryn\Jacobn\ExtensionFrameworkBundle\Configurators\AppConfigurator;
use Harryn\Jacobn\ExtensionFrameworkBundle\Configurators\PackageConfigurator;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Package\PackageInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Routing\RoutingResourceInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Application\ApplicationInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Package\ConfigurablePackageInterface;
use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Package\ContainerBuilderAwarePackageInterface;


class ContainerExtension extends Extension
{
    public function getAlias()
    {
        return 'uvdesk_extensions';
    }

    public function getConfiguration(array $configs, ContainerBuilder $container)
    {
        return new Configuration();
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        // Define parameters
        foreach ($this->processConfiguration($this->getConfiguration($configs, $container), $configs) as $param => $value) {
            switch ($param) {
                case 'dir':
                    $container->setParameter("uvdesk_extensions.dir", $value);
                    break;
                default:
                    break;
            }
        }

        // Define services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        
        // Compile modules
        $env = $container->getParameter('kernel.environment');
        $path = $container->getParameter("kernel.project_dir") . "/jacobn.lock";
        $mappingResource = $container->findDefinition(MappingResource::class);
        $availableConfigurations = $this->parsePackageConfigurations($container->getParameter("kernel.project_dir") . "/config/extensions");
        
        foreach ($this->getCachedPackages($path) as $attributes) {
            $reference = current(array_keys($attributes['package']));
            $supportedEnvironments = $attributes['package'][$reference];

            // Check if package is supported in the current environment
            if (in_array('all', $supportedEnvironments) || in_array($env, $supportedEnvironments)) {
                $class = new \ReflectionClass($reference);
                
                if (!$class->implementsInterface(PackageInterface::class)) {
                    throw new \Exception("Class $reference could not be registered as a package. Please check that it implements the " . PackageInterface::class . " interface.");
                }

                if ($class->implementsInterface(ConfigurablePackageInterface::class)) {
                    $schema = $class->newInstanceWithoutConstructor()->getConfiguration();

                    if (!empty($schema)) {
                        $qualifiedName = str_replace(['/', '-'], '_', $attributes['name']);
    
                        if (empty($availableConfigurations[$qualifiedName])) {
                            throw new \Exception("No available configurations found for package '" . $attributes['name'] . "'");
                        }

                        // Validate package configuration params
                        $packageConfigurations = $this->processConfiguration($schema, $availableConfigurations[$qualifiedName]);

                        // Unset and cache package params for later re-use
                        unset($availableConfigurations[$qualifiedName]);
                        $mappingResource->addMethodCall('setPackageConfigurations', array($reference, $packageConfigurations));
                    }
                }

                // Prepare package for configuration
                $this->loadPackageServices($class->getFileName(), $loader);

                if ($container->hasDefinition($reference)) {
                    $mappingResource->addMethodCall('setPackageMetadata', array($reference, $attributes));
                }
            }
        }

        if (!empty($availableConfigurations)) {
            // @TODO: Raise exception about invalid configurations
            dump('Invalid configurations found');
            dump($availableConfigurations);
            die;
        }

        // Configure services
        $container->registerForAutoconfiguration(PackageInterface::class)->addTag(PackageInterface::class)->setLazy(true)->setPublic(true);
        $container->registerForAutoconfiguration(ApplicationInterface::class)->addTag(ApplicationInterface::class)->setLazy(true)->setPublic(true);
        $container->registerForAutoconfiguration(ContainerBuilderAwarePackageInterface::class)->addTag(ContainerBuilderAwarePackageInterface::class);
        $container->registerForAutoconfiguration(RoutingResourceInterface::class)->addTag(RoutingResourceInterface::class);
    }

    private function getCachedPackages($path) : array
    {
        try {
            if (file_exists($path)) {
                return json_decode(file_get_contents($path), true)['packages'] ?? [];
            }
        } catch (\Exception $e) {
            // Skip module compilation ...
        }

        return [];
    }

    private function loadPackageServices($classPath, YamlFileLoader $loader)
    {
        $path = dirname($classPath) . "/Resources/config/services.yaml";

        if (file_exists($path)) {
            $loader->load($path);
        }
    }

    private function parsePackageConfigurations($prefix) : array
    {
        $configs = [];

        if (file_exists($prefix) && is_dir($prefix)) {
            foreach (array_diff(scandir($prefix), ['.', '..']) as $extensionConfig) {
                $path = "$prefix/$extensionConfig";
    
                if (!is_dir($path) && 'yaml' === pathinfo($path, PATHINFO_EXTENSION)) {
                    $configs[pathinfo($path, PATHINFO_FILENAME)] = Yaml::parseFile($path);
                }
            }
        }

        return $configs;
    }
}
