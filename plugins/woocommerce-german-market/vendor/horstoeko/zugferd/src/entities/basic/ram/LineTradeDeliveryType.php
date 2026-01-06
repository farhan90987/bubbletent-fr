<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing LineTradeDeliveryType
 *
 * XSD Type: LineTradeDeliveryType
 */
class LineTradeDeliveryType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\QuantityType $billedQuantity
     */
    private $billedQuantity = null;

    /**
     * Gets as billedQuantity
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\QuantityType
     */
    public function getBilledQuantity()
    {
        return $this->billedQuantity;
    }

    /**
     * Sets a new billedQuantity
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\QuantityType $billedQuantity
     * @return self
     */
    public function setBilledQuantity(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\QuantityType $billedQuantity)
    {
        $this->billedQuantity = $billedQuantity;
        return $this;
    }
}
