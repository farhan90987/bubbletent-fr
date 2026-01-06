<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Metadata\Driver;

use MarketPress\German_Market\JMS\Serializer\Metadata\ClassMetadata;
use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;
use MarketPress\German_Market\JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use MarketPress\German_Market\Metadata\ClassMetadata as BaseClassMetadata;
use MarketPress\German_Market\Metadata\Driver\DriverInterface;

class NullDriver implements DriverInterface
{
    /**
     * @var PropertyNamingStrategyInterface
     */
    private $namingStrategy;

    public function __construct(PropertyNamingStrategyInterface $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?BaseClassMetadata
    {
        $classMetadata = new ClassMetadata($name = $class->name);
        $fileResource =  $class->getFilename();
        if (false !== $fileResource) {
            $classMetadata->fileResources[] = $fileResource;
        }

        foreach ($class->getProperties() as $property) {
            if ($property->class !== $name || (isset($property->info) && $property->info['class'] !== $name)) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata($name, $property->getName());

            if (!$propertyMetadata->serializedName) {
                $propertyMetadata->serializedName = $this->namingStrategy->translateName($propertyMetadata);
            }

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }
}
