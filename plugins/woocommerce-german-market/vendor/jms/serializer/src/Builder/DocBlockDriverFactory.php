<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Builder;

use Doctrine\Common\Annotations\Reader;
use MarketPress\German_Market\JMS\Serializer\Metadata\Driver\DocBlockDriver;
use MarketPress\German_Market\JMS\Serializer\Type\ParserInterface;
use MarketPress\German_Market\Metadata\Driver\DriverInterface;

class DocBlockDriverFactory implements DriverFactoryInterface
{
    /**
     * @var DriverFactoryInterface
     */
    private $driverFactoryToDecorate;
    /**
     * @var ParserInterface|null
     */
    private $typeParser;

    public function __construct(DriverFactoryInterface $driverFactoryToDecorate, ?ParserInterface $typeParser = null)
    {
        $this->driverFactoryToDecorate = $driverFactoryToDecorate;
        $this->typeParser = $typeParser;
    }

    public function createDriver(array $metadataDirs, ?Reader $annotationReader = null): DriverInterface
    {
        $driver = $this->driverFactoryToDecorate->createDriver($metadataDirs, $annotationReader);

        return new DocBlockDriver($driver, $this->typeParser);
    }
}
