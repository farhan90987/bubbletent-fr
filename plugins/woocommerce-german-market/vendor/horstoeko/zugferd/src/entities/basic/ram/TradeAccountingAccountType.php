<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing TradeAccountingAccountType
 *
 * XSD Type: TradeAccountingAccountType
 */
class TradeAccountingAccountType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $iD
     */
    private $iD = null;

    /**
     * Gets as iD
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType
     */
    public function getID()
    {
        return $this->iD;
    }

    /**
     * Sets a new iD
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $iD
     * @return self
     */
    public function setID(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $iD)
    {
        $this->iD = $iD;
        return $this;
    }
}
