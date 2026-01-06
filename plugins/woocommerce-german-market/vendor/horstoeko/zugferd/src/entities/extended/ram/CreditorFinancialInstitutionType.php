<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing CreditorFinancialInstitutionType
 *
 * XSD Type: CreditorFinancialInstitutionType
 */
class CreditorFinancialInstitutionType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $bICID
     */
    private $bICID = null;

    /**
     * Gets as bICID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getBICID()
    {
        return $this->bICID;
    }

    /**
     * Sets a new bICID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $bICID
     * @return self
     */
    public function setBICID(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $bICID)
    {
        $this->bICID = $bICID;
        return $this;
    }
}
