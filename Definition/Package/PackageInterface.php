<?php

namespace Harryn\Jacobn\ExtensionFrameworkBundle\Definition\Package;

interface PackageInterface
{
    public function setMetadata(PackageMetadata $metadata) : PackageInterface;
    public function getMetadata() : PackageMetadata;
}
