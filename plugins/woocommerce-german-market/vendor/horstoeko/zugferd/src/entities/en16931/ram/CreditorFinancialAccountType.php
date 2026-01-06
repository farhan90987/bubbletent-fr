<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing CreditorFinancialAccountType
 *
 * XSD Type: CreditorFinancialAccountType
 */
class CreditorFinancialAccountType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $iBANID
     */
    private $iBANID = null;

    /**
     * @var string $accountName
     */
    private $accountName = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $proprietaryID
     */
    private $proprietaryID = null;

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
    public function setIBANID(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $iBANID = null)
    {
        $this->iBANID = $iBANID;
        return $this;
    }

    /**
     * Gets as accountName
     *
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Sets a new accountName
     *
     * @param  string $accountName
     * @return self
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
        return $this;
    }

    /**
     * Gets as proprietaryID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType
     */
    public function getProprietaryID()
    {
        return $this->proprietaryID;
    }

    /**
     * Sets a new proprietaryID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $proprietaryID
     * @return self
     */
    public function setProprietaryID(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $proprietaryID = null)
    {
        $this->proprietaryID = $proprietaryID;
        return $this;
    }
}
