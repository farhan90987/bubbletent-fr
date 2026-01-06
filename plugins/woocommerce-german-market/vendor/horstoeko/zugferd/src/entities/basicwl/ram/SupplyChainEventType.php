<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram;

/**
 * Class representing SupplyChainEventType
 *
 * XSD Type: SupplyChainEventType
 */
class SupplyChainEventType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\DateTimeType $occurrenceDateTime
     */
    private $occurrenceDateTime = null;

    /**
     * Gets as occurrenceDateTime
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\DateTimeType
     */
    public function getOccurrenceDateTime()
    {
        return $this->occurrenceDateTime;
    }

    /**
     * Sets a new occurrenceDateTime
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\DateTimeType $occurrenceDateTime
     * @return self
     */
    public function setOccurrenceDateTime(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\DateTimeType $occurrenceDateTime)
    {
        $this->occurrenceDateTime = $occurrenceDateTime;
        return $this;
    }
}
