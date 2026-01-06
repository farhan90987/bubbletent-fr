<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram;

/**
 * Class representing TradePartyType
 *
 * XSD Type: TradePartyType
 */
class TradePartyType
{

    /**
     * @var string $name
     */
    private $name = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\LegalOrganizationType $specifiedLegalOrganization
     */
    private $specifiedLegalOrganization = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradeAddressType $postalTradeAddress
     */
    private $postalTradeAddress = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TaxRegistrationType[] $specifiedTaxRegistration
     */
    private $specifiedTaxRegistration = [
        
    ];

    /**
     * Gets as name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a new name
     *
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets as specifiedLegalOrganization
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\LegalOrganizationType
     */
    public function getSpecifiedLegalOrganization()
    {
        return $this->specifiedLegalOrganization;
    }

    /**
     * Sets a new specifiedLegalOrganization
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\LegalOrganizationType $specifiedLegalOrganization
     * @return self
     */
    public function setSpecifiedLegalOrganization(?\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\LegalOrganizationType $specifiedLegalOrganization = null)
    {
        $this->specifiedLegalOrganization = $specifiedLegalOrganization;
        return $this;
    }

    /**
     * Gets as postalTradeAddress
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradeAddressType
     */
    public function getPostalTradeAddress()
    {
        return $this->postalTradeAddress;
    }

    /**
     * Sets a new postalTradeAddress
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradeAddressType $postalTradeAddress
     * @return self
     */
    public function setPostalTradeAddress(?\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradeAddressType $postalTradeAddress = null)
    {
        $this->postalTradeAddress = $postalTradeAddress;
        return $this;
    }

    /**
     * Adds as specifiedTaxRegistration
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TaxRegistrationType $specifiedTaxRegistration
     */
    public function addToSpecifiedTaxRegistration(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TaxRegistrationType $specifiedTaxRegistration)
    {
        $this->specifiedTaxRegistration[] = $specifiedTaxRegistration;
        return $this;
    }

    /**
     * isset specifiedTaxRegistration
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetSpecifiedTaxRegistration($index)
    {
        return isset($this->specifiedTaxRegistration[$index]);
    }

    /**
     * unset specifiedTaxRegistration
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetSpecifiedTaxRegistration($index)
    {
        unset($this->specifiedTaxRegistration[$index]);
    }

    /**
     * Gets as specifiedTaxRegistration
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TaxRegistrationType[]
     */
    public function getSpecifiedTaxRegistration()
    {
        return $this->specifiedTaxRegistration;
    }

    /**
     * Sets a new specifiedTaxRegistration
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TaxRegistrationType[] $specifiedTaxRegistration
     * @return self
     */
    public function setSpecifiedTaxRegistration(?array $specifiedTaxRegistration = null)
    {
        $this->specifiedTaxRegistration = $specifiedTaxRegistration;
        return $this;
    }
}
