<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing LineTradeSettlementType
 *
 * XSD Type: LineTradeSettlementType
 */
class LineTradeSettlementType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType[] $applicableTradeTax
     */
    private $applicableTradeTax = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SpecifiedPeriodType $billingSpecifiedPeriod
     */
    private $billingSpecifiedPeriod = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAllowanceChargeType[] $specifiedTradeAllowanceCharge
     */
    private $specifiedTradeAllowanceCharge = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeSettlementLineMonetarySummationType $specifiedTradeSettlementLineMonetarySummation
     */
    private $specifiedTradeSettlementLineMonetarySummation = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $invoiceReferencedDocument
     */
    private $invoiceReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType[] $additionalReferencedDocument
     */
    private $additionalReferencedDocument = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount
     */
    private $receivableSpecifiedTradeAccountingAccount = null;

    /**
     * Adds as applicableTradeTax
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType $applicableTradeTax
     */
    public function addToApplicableTradeTax(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType $applicableTradeTax)
    {
        $this->applicableTradeTax[] = $applicableTradeTax;
        return $this;
    }

    /**
     * isset applicableTradeTax
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetApplicableTradeTax($index)
    {
        return isset($this->applicableTradeTax[$index]);
    }

    /**
     * unset applicableTradeTax
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetApplicableTradeTax($index)
    {
        unset($this->applicableTradeTax[$index]);
    }

    /**
     * Gets as applicableTradeTax
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType[]
     */
    public function getApplicableTradeTax()
    {
        return $this->applicableTradeTax;
    }

    /**
     * Sets a new applicableTradeTax
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeTaxType[] $applicableTradeTax
     * @return self
     */
    public function setApplicableTradeTax(array $applicableTradeTax)
    {
        $this->applicableTradeTax = $applicableTradeTax;
        return $this;
    }

    /**
     * Gets as billingSpecifiedPeriod
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SpecifiedPeriodType
     */
    public function getBillingSpecifiedPeriod()
    {
        return $this->billingSpecifiedPeriod;
    }

    /**
     * Sets a new billingSpecifiedPeriod
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SpecifiedPeriodType $billingSpecifiedPeriod
     * @return self
     */
    public function setBillingSpecifiedPeriod(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SpecifiedPeriodType $billingSpecifiedPeriod = null)
    {
        $this->billingSpecifiedPeriod = $billingSpecifiedPeriod;
        return $this;
    }

    /**
     * Adds as specifiedTradeAllowanceCharge
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAllowanceChargeType $specifiedTradeAllowanceCharge
     */
    public function addToSpecifiedTradeAllowanceCharge(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAllowanceChargeType $specifiedTradeAllowanceCharge)
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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAllowanceChargeType[]
     */
    public function getSpecifiedTradeAllowanceCharge()
    {
        return $this->specifiedTradeAllowanceCharge;
    }

    /**
     * Sets a new specifiedTradeAllowanceCharge
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAllowanceChargeType[] $specifiedTradeAllowanceCharge
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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeSettlementLineMonetarySummationType
     */
    public function getSpecifiedTradeSettlementLineMonetarySummation()
    {
        return $this->specifiedTradeSettlementLineMonetarySummation;
    }

    /**
     * Sets a new specifiedTradeSettlementLineMonetarySummation
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeSettlementLineMonetarySummationType $specifiedTradeSettlementLineMonetarySummation
     * @return self
     */
    public function setSpecifiedTradeSettlementLineMonetarySummation(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeSettlementLineMonetarySummationType $specifiedTradeSettlementLineMonetarySummation)
    {
        $this->specifiedTradeSettlementLineMonetarySummation = $specifiedTradeSettlementLineMonetarySummation;
        return $this;
    }

    /**
     * Gets as invoiceReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getInvoiceReferencedDocument()
    {
        return $this->invoiceReferencedDocument;
    }

    /**
     * Sets a new invoiceReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $invoiceReferencedDocument
     * @return self
     */
    public function setInvoiceReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $invoiceReferencedDocument = null)
    {
        $this->invoiceReferencedDocument = $invoiceReferencedDocument;
        return $this;
    }

    /**
     * Adds as additionalReferencedDocument
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $additionalReferencedDocument
     */
    public function addToAdditionalReferencedDocument(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $additionalReferencedDocument)
    {
        $this->additionalReferencedDocument[] = $additionalReferencedDocument;
        return $this;
    }

    /**
     * isset additionalReferencedDocument
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetAdditionalReferencedDocument($index)
    {
        return isset($this->additionalReferencedDocument[$index]);
    }

    /**
     * unset additionalReferencedDocument
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetAdditionalReferencedDocument($index)
    {
        unset($this->additionalReferencedDocument[$index]);
    }

    /**
     * Gets as additionalReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType[]
     */
    public function getAdditionalReferencedDocument()
    {
        return $this->additionalReferencedDocument;
    }

    /**
     * Sets a new additionalReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType[] $additionalReferencedDocument
     * @return self
     */
    public function setAdditionalReferencedDocument(?array $additionalReferencedDocument = null)
    {
        $this->additionalReferencedDocument = $additionalReferencedDocument;
        return $this;
    }

    /**
     * Gets as receivableSpecifiedTradeAccountingAccount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAccountingAccountType
     */
    public function getReceivableSpecifiedTradeAccountingAccount()
    {
        return $this->receivableSpecifiedTradeAccountingAccount;
    }

    /**
     * Sets a new receivableSpecifiedTradeAccountingAccount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount
     * @return self
     */
    public function setReceivableSpecifiedTradeAccountingAccount(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount = null)
    {
        $this->receivableSpecifiedTradeAccountingAccount = $receivableSpecifiedTradeAccountingAccount;
        return $this;
    }
}
