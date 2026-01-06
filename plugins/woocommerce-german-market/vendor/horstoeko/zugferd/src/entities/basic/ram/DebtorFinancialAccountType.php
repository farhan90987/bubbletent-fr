<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing DebtorFinancialAccountType
 *
 * XSD Type: DebtorFinancialAccountType
 */
class DebtorFinancialAccountType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $iBANID
     */
    private $iBANID = null;

    /**
     * Gets as iBANID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType
     */
    public function getIBANID()
    {
        return $this->iBANID;
    }

    /**
     * Sets a new iBANID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $iBANID
     * @return self
     */
    public function setIBANID(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $iBANID)
    {
        $this->iBANID = $iBANID;
        return $this;
    }
}
