<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\minimum\rsm;

/**
 * Class representing CrossIndustryInvoiceType
 *
 * XSD Type: CrossIndustryInvoiceType
 */
class CrossIndustryInvoiceType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentContextType $exchangedDocumentContext
     */
    private $exchangedDocumentContext = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentType $exchangedDocument
     */
    private $exchangedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\SupplyChainTradeTransactionType $supplyChainTradeTransaction
     */
    private $supplyChainTradeTransaction = null;

    /**
     * Gets as exchangedDocumentContext
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentContextType
     */
    public function getExchangedDocumentContext()
    {
        return $this->exchangedDocumentContext;
    }

    /**
     * Sets a new exchangedDocumentContext
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentContextType $exchangedDocumentContext
     * @return self
     */
    public function setExchangedDocumentContext(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentContextType $exchangedDocumentContext)
    {
        $this->exchangedDocumentContext = $exchangedDocumentContext;
        return $this;
    }

    /**
     * Gets as exchangedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentType
     */
    public function getExchangedDocument()
    {
        return $this->exchangedDocument;
    }

    /**
     * Sets a new exchangedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentType $exchangedDocument
     * @return self
     */
    public function setExchangedDocument(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ExchangedDocumentType $exchangedDocument)
    {
        $this->exchangedDocument = $exchangedDocument;
        return $this;
    }

    /**
     * Gets as supplyChainTradeTransaction
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\SupplyChainTradeTransactionType
     */
    public function getSupplyChainTradeTransaction()
    {
        return $this->supplyChainTradeTransaction;
    }

    /**
     * Sets a new supplyChainTradeTransaction
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\SupplyChainTradeTransactionType $supplyChainTradeTransaction
     * @return self
     */
    public function setSupplyChainTradeTransaction(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\SupplyChainTradeTransactionType $supplyChainTradeTransaction)
    {
        $this->supplyChainTradeTransaction = $supplyChainTradeTransaction;
        return $this;
    }
}
