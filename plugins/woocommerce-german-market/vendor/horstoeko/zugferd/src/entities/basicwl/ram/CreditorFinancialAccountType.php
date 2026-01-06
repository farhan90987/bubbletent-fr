<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram;

/**
 * Class representing CreditorFinancialAccountType
 *
 * XSD Type: CreditorFinancialAccountType
 */
class CreditorFinancialAccountType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $iBANID
     */
    private $iBANID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $proprietaryID
     */
    private $proprietaryID = null;

    /**
     * Gets as iBANID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType
     */
    public function getIBANID()
    {
        return $this->iBANID;
    }

    /**
     * Sets a new iBANID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $iBANID
     * @return self
     */
    public function setIBANID(?\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $iBANID = null)
    {
        $this->iBANID = $iBANID;
        return $this;
    }

    /**
     * Gets as proprietaryID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType
     */
    public function getProprietaryID()
    {
        return $this->proprietaryID;
    }

    /**
     * Sets a new proprietaryID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $proprietaryID
     * @return self
     */
    public function setProprietaryID(?\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $proprietaryID = null)
    {
        $this->proprietaryID = $proprietaryID;
        return $this;
    }
}
