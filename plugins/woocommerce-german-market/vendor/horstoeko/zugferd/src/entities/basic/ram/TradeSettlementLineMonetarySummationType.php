<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing TradeSettlementLineMonetarySummationType
 *
 * XSD Type: TradeSettlementLineMonetarySummationType
 */
class TradeSettlementLineMonetarySummationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\AmountType $lineTotalAmount
     */
    private $lineTotalAmount = null;

    /**
     * Gets as lineTotalAmount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\AmountType
     */
    public function getLineTotalAmount()
    {
        return $this->lineTotalAmount;
    }

    /**
     * Sets a new lineTotalAmount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\AmountType $lineTotalAmount
     * @return self
     */
    public function setLineTotalAmount(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\AmountType $lineTotalAmount)
    {
        $this->lineTotalAmount = $lineTotalAmount;
        return $this;
    }
}
