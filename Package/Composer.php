<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\Package;

use Harryn\Jacobn\PackageManager\Composer\ComposerPackage;
use Harryn\Jacobn\PackageManager\Composer\ComposerPackageExtension;

class Composer extends ComposerPackageExtension
{
    public function loadConfiguration()
    {
        $composerPackage = new ComposerPackage();
        $composerPackage
            ->movePackageConfig('config/packages/uvdesk_extensions.yaml', 'Templates/config.yaml')
            ->combineProjectConfig('config/routes.yaml', 'Templates/routes.yaml');
        
        return $composerPackage;
    }
}
