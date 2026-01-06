<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram;

/**
 * Class representing SupplyChainTradeTransactionType
 *
 * XSD Type: SupplyChainTradeTransactionType
 */
class SupplyChainTradeTransactionType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeAgreementType $applicableHeaderTradeAgreement
     */
    private $applicableHeaderTradeAgreement = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeDeliveryType $applicableHeaderTradeDelivery
     */
    private $applicableHeaderTradeDelivery = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeSettlementType $applicableHeaderTradeSettlement
     */
    private $applicableHeaderTradeSettlement = null;

    /**
     * Gets as applicableHeaderTradeAgreement
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeAgreementType
     */
    public function getApplicableHeaderTradeAgreement()
    {
        return $this->applicableHeaderTradeAgreement;
    }

    /**
     * Sets a new applicableHeaderTradeAgreement
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeAgreementType $applicableHeaderTradeAgreement
     * @return self
     */
    public function setApplicableHeaderTradeAgreement(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeAgreementType $applicableHeaderTradeAgreement)
    {
        $this->applicableHeaderTradeAgreement = $applicableHeaderTradeAgreement;
        return $this;
    }

    /**
     * Gets as applicableHeaderTradeDelivery
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeDeliveryType
     */
    public function getApplicableHeaderTradeDelivery()
    {
        return $this->applicableHeaderTradeDelivery;
    }

    /**
     * Sets a new applicableHeaderTradeDelivery
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeDeliveryType $applicableHeaderTradeDelivery
     * @return self
     */
    public function setApplicableHeaderTradeDelivery(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeDeliveryType $applicableHeaderTradeDelivery)
    {
        $this->applicableHeaderTradeDelivery = $applicableHeaderTradeDelivery;
        return $this;
    }

    /**
     * Gets as applicableHeaderTradeSettlement
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeSettlementType
     */
    public function getApplicableHeaderTradeSettlement()
    {
        return $this->applicableHeaderTradeSettlement;
    }

    /**
     * Sets a new applicableHeaderTradeSettlement
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeSettlementType $applicableHeaderTradeSettlement
     * @return self
     */
    public function setApplicableHeaderTradeSettlement(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram\HeaderTradeSettlementType $applicableHeaderTradeSettlement)
    {
        $this->applicableHeaderTradeSettlement = $applicableHeaderTradeSettlement;
        return $this;
    }
}
