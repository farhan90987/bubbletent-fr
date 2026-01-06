<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\rsm;

/**
 * Class representing CrossIndustryInvoiceType
 *
 * XSD Type: CrossIndustryInvoiceType
 */
class CrossIndustryInvoiceType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentContextType $exchangedDocumentContext
     */
    private $exchangedDocumentContext = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentType $exchangedDocument
     */
    private $exchangedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainTradeTransactionType $supplyChainTradeTransaction
     */
    private $supplyChainTradeTransaction = null;

    /**
     * Gets as exchangedDocumentContext
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentContextType
     */
    public function getExchangedDocumentContext()
    {
        return $this->exchangedDocumentContext;
    }

    /**
     * Sets a new exchangedDocumentContext
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentContextType $exchangedDocumentContext
     * @return self
     */
    public function setExchangedDocumentContext(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentContextType $exchangedDocumentContext)
    {
        $this->exchangedDocumentContext = $exchangedDocumentContext;
        return $this;
    }

    /**
     * Gets as exchangedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentType
     */
    public function getExchangedDocument()
    {
        return $this->exchangedDocument;
    }

    /**
     * Sets a new exchangedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentType $exchangedDocument
     * @return self
     */
    public function setExchangedDocument(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ExchangedDocumentType $exchangedDocument)
    {
        $this->exchangedDocument = $exchangedDocument;
        return $this;
    }

    /**
     * Gets as supplyChainTradeTransaction
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainTradeTransactionType
     */
    public function getSupplyChainTradeTransaction()
    {
        return $this->supplyChainTradeTransaction;
    }

    /**
     * Sets a new supplyChainTradeTransaction
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainTradeTransactionType $supplyChainTradeTransaction
     * @return self
     */
    public function setSupplyChainTradeTransaction(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainTradeTransactionType $supplyChainTradeTransaction)
    {
        $this->supplyChainTradeTransaction = $supplyChainTradeTransaction;
        return $this;
    }
}
