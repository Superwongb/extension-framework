<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Application;

use Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Package\PackageInterface;

interface ApplicationInterface
{
    public static function getMetadata() : ApplicationMetadata;

    public function setPackage(PackageInterface $package) : ApplicationInterface;
    
    public function getPackage() : PackageInterface;
}
