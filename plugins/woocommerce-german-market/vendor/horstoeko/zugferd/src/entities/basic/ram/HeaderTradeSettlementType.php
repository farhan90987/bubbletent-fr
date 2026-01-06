<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing HeaderTradeSettlementType
 *
 * XSD Type: HeaderTradeSettlementType
 */
class HeaderTradeSettlementType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $creditorReferenceID
     */
    private $creditorReferenceID = null;

    /**
     * @var string $paymentReference
     */
    private $paymentReference = null;

    /**
     * @var string $taxCurrencyCode
     */
    private $taxCurrencyCode = null;

    /**
     * @var string $invoiceCurrencyCode
     */
    private $invoiceCurrencyCode = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePartyType $payeeTradeParty
     */
    private $payeeTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementPaymentMeansType[] $specifiedTradeSettlementPaymentMeans
     */
    private $specifiedTradeSettlementPaymentMeans = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeTaxType[] $applicableTradeTax
     */
    private $applicableTradeTax = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\SpecifiedPeriodType $billingSpecifiedPeriod
     */
    private $billingSpecifiedPeriod = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAllowanceChargeType[] $specifiedTradeAllowanceCharge
     */
    private $specifiedTradeAllowanceCharge = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePaymentTermsType $specifiedTradePaymentTerms
     */
    private $specifiedTradePaymentTerms = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementHeaderMonetarySummationType $specifiedTradeSettlementHeaderMonetarySummation
     */
    private $specifiedTradeSettlementHeaderMonetarySummation = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\ReferencedDocumentType[] $invoiceReferencedDocument
     */
    private $invoiceReferencedDocument = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount
     */
    private $receivableSpecifiedTradeAccountingAccount = null;

    /**
     * Gets as creditorReferenceID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType
     */
    public function getCreditorReferenceID()
    {
        return $this->creditorReferenceID;
    }

    /**
     * Sets a new creditorReferenceID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $creditorReferenceID
     * @return self
     */
    public function setCreditorReferenceID(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $creditorReferenceID = null)
    {
        $this->creditorReferenceID = $creditorReferenceID;
        return $this;
    }

    /**
     * Gets as paymentReference
     *
     * @return string
     */
    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    /**
     * Sets a new paymentReference
     *
     * @param  string $paymentReference
     * @return self
     */
    public function setPaymentReference($paymentReference)
    {
        $this->paymentReference = $paymentReference;
        return $this;
    }

    /**
     * Gets as taxCurrencyCode
     *
     * @return string
     */
    public function getTaxCurrencyCode()
    {
        return $this->taxCurrencyCode;
    }

    /**
     * Sets a new taxCurrencyCode
     *
     * @param  string $taxCurrencyCode
     * @return self
     */
    public function setTaxCurrencyCode($taxCurrencyCode)
    {
        $this->taxCurrencyCode = $taxCurrencyCode;
        return $this;
    }

    /**
     * Gets as invoiceCurrencyCode
     *
     * @return string
     */
    public function getInvoiceCurrencyCode()
    {
        return $this->invoiceCurrencyCode;
    }

    /**
     * Sets a new invoiceCurrencyCode
     *
     * @param  string $invoiceCurrencyCode
     * @return self
     */
    public function setInvoiceCurrencyCode($invoiceCurrencyCode)
    {
        $this->invoiceCurrencyCode = $invoiceCurrencyCode;
        return $this;
    }

    /**
     * Gets as payeeTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePartyType
     */
    public function getPayeeTradeParty()
    {
        return $this->payeeTradeParty;
    }

    /**
     * Sets a new payeeTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePartyType $payeeTradeParty
     * @return self
     */
    public function setPayeeTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePartyType $payeeTradeParty = null)
    {
        $this->payeeTradeParty = $payeeTradeParty;
        return $this;
    }

    /**
     * Adds as specifiedTradeSettlementPaymentMeans
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementPaymentMeansType $specifiedTradeSettlementPaymentMeans
     */
    public function addToSpecifiedTradeSettlementPaymentMeans(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementPaymentMeansType $specifiedTradeSettlementPaymentMeans)
    {
        $this->specifiedTradeSettlementPaymentMeans[] = $specifiedTradeSettlementPaymentMeans;
        return $this;
    }

    /**
     * isset specifiedTradeSettlementPaymentMeans
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetSpecifiedTradeSettlementPaymentMeans($index)
    {
        return isset($this->specifiedTradeSettlementPaymentMeans[$index]);
    }

    /**
     * unset specifiedTradeSettlementPaymentMeans
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetSpecifiedTradeSettlementPaymentMeans($index)
    {
        unset($this->specifiedTradeSettlementPaymentMeans[$index]);
    }

    /**
     * Gets as specifiedTradeSettlementPaymentMeans
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementPaymentMeansType[]
     */
    public function getSpecifiedTradeSettlementPaymentMeans()
    {
        return $this->specifiedTradeSettlementPaymentMeans;
    }

    /**
     * Sets a new specifiedTradeSettlementPaymentMeans
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementPaymentMeansType[] $specifiedTradeSettlementPaymentMeans
     * @return self
     */
    public function setSpecifiedTradeSettlementPaymentMeans(?array $specifiedTradeSettlementPaymentMeans = null)
    {
        $this->specifiedTradeSettlementPaymentMeans = $specifiedTradeSettlementPaymentMeans;
        return $this;
    }

    /**
     * Adds as applicableTradeTax
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeTaxType $applicableTradeTax
     */
    public function addToApplicableTradeTax(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeTaxType $applicableTradeTax)
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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeTaxType[]
     */
    public function getApplicableTradeTax()
    {
        return $this->applicableTradeTax;
    }

    /**
     * Sets a new applicableTradeTax
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeTaxType[] $applicableTradeTax
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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\SpecifiedPeriodType
     */
    public function getBillingSpecifiedPeriod()
    {
        return $this->billingSpecifiedPeriod;
    }

    /**
     * Sets a new billingSpecifiedPeriod
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\SpecifiedPeriodType $billingSpecifiedPeriod
     * @return self
     */
    public function setBillingSpecifiedPeriod(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\SpecifiedPeriodType $billingSpecifiedPeriod = null)
    {
        $this->billingSpecifiedPeriod = $billingSpecifiedPeriod;
        return $this;
    }

    /**
     * Adds as specifiedTradeAllowanceCharge
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAllowanceChargeType $specifiedTradeAllowanceCharge
     */
    public function addToSpecifiedTradeAllowanceCharge(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAllowanceChargeType $specifiedTradeAllowanceCharge)
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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAllowanceChargeType[]
     */
    public function getSpecifiedTradeAllowanceCharge()
    {
        return $this->specifiedTradeAllowanceCharge;
    }

    /**
     * Sets a new specifiedTradeAllowanceCharge
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAllowanceChargeType[] $specifiedTradeAllowanceCharge
     * @return self
     */
    public function setSpecifiedTradeAllowanceCharge(?array $specifiedTradeAllowanceCharge = null)
    {
        $this->specifiedTradeAllowanceCharge = $specifiedTradeAllowanceCharge;
        return $this;
    }

    /**
     * Gets as specifiedTradePaymentTerms
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePaymentTermsType
     */
    public function getSpecifiedTradePaymentTerms()
    {
        return $this->specifiedTradePaymentTerms;
    }

    /**
     * Sets a new specifiedTradePaymentTerms
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePaymentTermsType $specifiedTradePaymentTerms
     * @return self
     */
    public function setSpecifiedTradePaymentTerms(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradePaymentTermsType $specifiedTradePaymentTerms = null)
    {
        $this->specifiedTradePaymentTerms = $specifiedTradePaymentTerms;
        return $this;
    }

    /**
     * Gets as specifiedTradeSettlementHeaderMonetarySummation
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementHeaderMonetarySummationType
     */
    public function getSpecifiedTradeSettlementHeaderMonetarySummation()
    {
        return $this->specifiedTradeSettlementHeaderMonetarySummation;
    }

    /**
     * Sets a new specifiedTradeSettlementHeaderMonetarySummation
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementHeaderMonetarySummationType $specifiedTradeSettlementHeaderMonetarySummation
     * @return self
     */
    public function setSpecifiedTradeSettlementHeaderMonetarySummation(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeSettlementHeaderMonetarySummationType $specifiedTradeSettlementHeaderMonetarySummation)
    {
        $this->specifiedTradeSettlementHeaderMonetarySummation = $specifiedTradeSettlementHeaderMonetarySummation;
        return $this;
    }

    /**
     * Adds as invoiceReferencedDocument
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\ReferencedDocumentType $invoiceReferencedDocument
     */
    public function addToInvoiceReferencedDocument(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\ReferencedDocumentType $invoiceReferencedDocument)
    {
        $this->invoiceReferencedDocument[] = $invoiceReferencedDocument;
        return $this;
    }

    /**
     * isset invoiceReferencedDocument
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetInvoiceReferencedDocument($index)
    {
        return isset($this->invoiceReferencedDocument[$index]);
    }

    /**
     * unset invoiceReferencedDocument
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetInvoiceReferencedDocument($index)
    {
        unset($this->invoiceReferencedDocument[$index]);
    }

    /**
     * Gets as invoiceReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\ReferencedDocumentType[]
     */
    public function getInvoiceReferencedDocument()
    {
        return $this->invoiceReferencedDocument;
    }

    /**
     * Sets a new invoiceReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\ReferencedDocumentType[] $invoiceReferencedDocument
     * @return self
     */
    public function setInvoiceReferencedDocument(?array $invoiceReferencedDocument = null)
    {
        $this->invoiceReferencedDocument = $invoiceReferencedDocument;
        return $this;
    }

    /**
     * Gets as receivableSpecifiedTradeAccountingAccount
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAccountingAccountType
     */
    public function getReceivableSpecifiedTradeAccountingAccount()
    {
        return $this->receivableSpecifiedTradeAccountingAccount;
    }

    /**
     * Sets a new receivableSpecifiedTradeAccountingAccount
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount
     * @return self
     */
    public function setReceivableSpecifiedTradeAccountingAccount(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram\TradeAccountingAccountType $receivableSpecifiedTradeAccountingAccount = null)
    {
        $this->receivableSpecifiedTradeAccountingAccount = $receivableSpecifiedTradeAccountingAccount;
        return $this;
    }
}
