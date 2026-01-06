<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing LineTradeSettlementType
 *
 * XSD Type: LineTradeSettlementType
 */
class LineTradeSettlementType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeTaxType $applicableTradeTax
     */
    private $applicableTradeTax = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SpecifiedPeriodType $billingSpecifiedPeriod
     */
    private $billingSpecifiedPeriod = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType[] $specifiedTradeAllowanceCharge
     */
    private $specifiedTradeAllowanceCharge = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeSettlementLineMonetarySummationType $specifiedTradeSettlementLineMonetarySummation
     */
    private $specifiedTradeSettlementLineMonetarySummation = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $additionalReferencedDocument
     */
    private $additionalReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount
     */
    private $receivableSpecifiedTradeAccountingAccount = null;

    /**
     * Gets as applicableTradeTax
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeTaxType
     */
    public function getApplicableTradeTax()
    {
        return $this->applicableTradeTax;
    }

    /**
     * Sets a new applicableTradeTax
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeTaxType $applicableTradeTax
     * @return self
     */
    public function setApplicableTradeTax(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeTaxType $applicableTradeTax)
    {
        $this->applicableTradeTax = $applicableTradeTax;
        return $this;
    }

    /**
     * Gets as billingSpecifiedPeriod
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SpecifiedPeriodType
     */
    public function getBillingSpecifiedPeriod()
    {
        return $this->billingSpecifiedPeriod;
    }

    /**
     * Sets a new billingSpecifiedPeriod
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SpecifiedPeriodType $billingSpecifiedPeriod
     * @return self
     */
    public function setBillingSpecifiedPeriod(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SpecifiedPeriodType $billingSpecifiedPeriod = null)
    {
        $this->billingSpecifiedPeriod = $billingSpecifiedPeriod;
        return $this;
    }

    /**
     * Adds as specifiedTradeAllowanceCharge
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType $specifiedTradeAllowanceCharge
     */
    public function addToSpecifiedTradeAllowanceCharge(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType $specifiedTradeAllowanceCharge)
    {
        $this->specifiedTradeAllowanceCharge[] = $specifiedTradeAllowanceCharge;
        return $this;
    }

    /**
     * isset specifiedTradeAllowanceCharge
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetSpecifiedTradeAllowanceCharge($index)
    {
        return isset($this->specifiedTradeAllowanceCharge[$index]);
    }

    /**
     * unset specifiedTradeAllowanceCharge
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetSpecifiedTradeAllowanceCharge($index)
    {
        unset($this->specifiedTradeAllowanceCharge[$index]);
    }

    /**
     * Gets as specifiedTradeAllowanceCharge
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType[]
     */
    public function getSpecifiedTradeAllowanceCharge()
    {
        return $this->specifiedTradeAllowanceCharge;
    }

    /**
     * Sets a new specifiedTradeAllowanceCharge
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAllowanceChargeType[] $specifiedTradeAllowanceCharge
     * @return self
     */
    public function setSpecifiedTradeAllowanceCharge(?array $specifiedTradeAllowanceCharge = null)
    {
        $this->specifiedTradeAllowanceCharge = $specifiedTradeAllowanceCharge;
        return $this;
    }

    /**
     * Gets as specifiedTradeSettlementLineMonetarySummation
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeSettlementLineMonetarySummationType
     */
    public function getSpecifiedTradeSettlementLineMonetarySummation()
    {
        return $this->specifiedTradeSettlementLineMonetarySummation;
    }

    /**
     * Sets a new specifiedTradeSettlementLineMonetarySummation
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeSettlementLineMonetarySummationType $specifiedTradeSettlementLineMonetarySummation
     * @return self
     */
    public function setSpecifiedTradeSettlementLineMonetarySummation(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeSettlementLineMonetarySummationType $specifiedTradeSettlementLineMonetarySummation)
    {
        $this->specifiedTradeSettlementLineMonetarySummation = $specifiedTradeSettlementLineMonetarySummation;
        return $this;
    }

    /**
     * Gets as additionalReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType
     */
    public function getAdditionalReferencedDocument()
    {
        return $this->additionalReferencedDocument;
    }

    /**
     * Sets a new additionalReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $additionalReferencedDocument
     * @return self
     */
    public function setAdditionalReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $additionalReferencedDocument = null)
    {
        $this->additionalReferencedDocument = $additionalReferencedDocument;
        return $this;
    }

    /**
     * Gets as receivableSpecifiedTradeAccountingAccount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAccountingAccountType
     */
    public function getReceivableSpecifiedTradeAccountingAccount()
    {
        return $this->receivableSpecifiedTradeAccountingAccount;
    }

    /**
     * Sets a new receivableSpecifiedTradeAccountingAccount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount
     * @return self
     */
    public function setReceivableSpecifiedTradeAccountingAccount(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount = null)
    {
        $this->receivableSpecifiedTradeAccountingAccount = $receivableSpecifiedTradeAccountingAccount;
        return $this;
    }
}
