<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing DebtorFinancialAccountType
 *
 * XSD Type: DebtorFinancialAccountType
 */
class DebtorFinancialAccountType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $iBANID
     */
    private $iBANID = null;

    /**
     * Gets as iBANID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType
     */
    public function getIBANID()
    {
        return $this->iBANID;
    }

    /**
     * Sets a new iBANID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $iBANID
     * @return self
     */
    public function setIBANID(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $iBANID)
    {
        $this->iBANID = $iBANID;
        return $this;
    }
}
