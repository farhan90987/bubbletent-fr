<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing HeaderTradeAgreementType
 *
 * XSD Type: HeaderTradeAgreementType
 */
class HeaderTradeAgreementType
{

    /**
     * @var string $buyerReference
     */
    private $buyerReference = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $sellerTradeParty
     */
    private $sellerTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerTradeParty
     */
    private $buyerTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $salesAgentTradeParty
     */
    private $salesAgentTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerTaxRepresentativeTradeParty
     */
    private $buyerTaxRepresentativeTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $sellerTaxRepresentativeTradeParty
     */
    private $sellerTaxRepresentativeTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $productEndUserTradeParty
     */
    private $productEndUserTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeDeliveryTermsType $applicableTradeDeliveryTerms
     */
    private $applicableTradeDeliveryTerms = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $sellerOrderReferencedDocument
     */
    private $sellerOrderReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $buyerOrderReferencedDocument
     */
    private $buyerOrderReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $quotationReferencedDocument
     */
    private $quotationReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $contractReferencedDocument
     */
    private $contractReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType[] $additionalReferencedDocument
     */
    private $additionalReferencedDocument = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerAgentTradeParty
     */
    private $buyerAgentTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProcuringProjectType $specifiedProcuringProject
     */
    private $specifiedProcuringProject = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType[] $ultimateCustomerOrderReferencedDocument
     */
    private $ultimateCustomerOrderReferencedDocument = [
        
    ];

    /**
     * Gets as buyerReference
     *
     * @return string
     */
    public function getBuyerReference()
    {
        return $this->buyerReference;
    }

    /**
     * Sets a new buyerReference
     *
     * @param  string $buyerReference
     * @return self
     */
    public function setBuyerReference($buyerReference)
    {
        $this->buyerReference = $buyerReference;
        return $this;
    }

    /**
     * Gets as sellerTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getSellerTradeParty()
    {
        return $this->sellerTradeParty;
    }

    /**
     * Sets a new sellerTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $sellerTradeParty
     * @return self
     */
    public function setSellerTradeParty(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $sellerTradeParty)
    {
        $this->sellerTradeParty = $sellerTradeParty;
        return $this;
    }

    /**
     * Gets as buyerTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getBuyerTradeParty()
    {
        return $this->buyerTradeParty;
    }

    /**
     * Sets a new buyerTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerTradeParty
     * @return self
     */
    public function setBuyerTradeParty(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerTradeParty)
    {
        $this->buyerTradeParty = $buyerTradeParty;
        return $this;
    }

    /**
     * Gets as salesAgentTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getSalesAgentTradeParty()
    {
        return $this->salesAgentTradeParty;
    }

    /**
     * Sets a new salesAgentTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $salesAgentTradeParty
     * @return self
     */
    public function setSalesAgentTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $salesAgentTradeParty = null)
    {
        $this->salesAgentTradeParty = $salesAgentTradeParty;
        return $this;
    }

    /**
     * Gets as buyerTaxRepresentativeTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getBuyerTaxRepresentativeTradeParty()
    {
        return $this->buyerTaxRepresentativeTradeParty;
    }

    /**
     * Sets a new buyerTaxRepresentativeTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerTaxRepresentativeTradeParty
     * @return self
     */
    public function setBuyerTaxRepresentativeTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerTaxRepresentativeTradeParty = null)
    {
        $this->buyerTaxRepresentativeTradeParty = $buyerTaxRepresentativeTradeParty;
        return $this;
    }

    /**
     * Gets as sellerTaxRepresentativeTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getSellerTaxRepresentativeTradeParty()
    {
        return $this->sellerTaxRepresentativeTradeParty;
    }

    /**
     * Sets a new sellerTaxRepresentativeTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $sellerTaxRepresentativeTradeParty
     * @return self
     */
    public function setSellerTaxRepresentativeTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $sellerTaxRepresentativeTradeParty = null)
    {
        $this->sellerTaxRepresentativeTradeParty = $sellerTaxRepresentativeTradeParty;
        return $this;
    }

    /**
     * Gets as productEndUserTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getProductEndUserTradeParty()
    {
        return $this->productEndUserTradeParty;
    }

    /**
     * Sets a new productEndUserTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $productEndUserTradeParty
     * @return self
     */
    public function setProductEndUserTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $productEndUserTradeParty = null)
    {
        $this->productEndUserTradeParty = $productEndUserTradeParty;
        return $this;
    }

    /**
     * Gets as applicableTradeDeliveryTerms
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeDeliveryTermsType
     */
    public function getApplicableTradeDeliveryTerms()
    {
        return $this->applicableTradeDeliveryTerms;
    }

    /**
     * Sets a new applicableTradeDeliveryTerms
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeDeliveryTermsType $applicableTradeDeliveryTerms
     * @return self
     */
    public function setApplicableTradeDeliveryTerms(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeDeliveryTermsType $applicableTradeDeliveryTerms = null)
    {
        $this->applicableTradeDeliveryTerms = $applicableTradeDeliveryTerms;
        return $this;
    }

    /**
     * Gets as sellerOrderReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getSellerOrderReferencedDocument()
    {
        return $this->sellerOrderReferencedDocument;
    }

    /**
     * Sets a new sellerOrderReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $sellerOrderReferencedDocument
     * @return self
     */
    public function setSellerOrderReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $sellerOrderReferencedDocument = null)
    {
        $this->sellerOrderReferencedDocument = $sellerOrderReferencedDocument;
        return $this;
    }

    /**
     * Gets as buyerOrderReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getBuyerOrderReferencedDocument()
    {
        return $this->buyerOrderReferencedDocument;
    }

    /**
     * Sets a new buyerOrderReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $buyerOrderReferencedDocument
     * @return self
     */
    public function setBuyerOrderReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $buyerOrderReferencedDocument = null)
    {
        $this->buyerOrderReferencedDocument = $buyerOrderReferencedDocument;
        return $this;
    }

    /**
     * Gets as quotationReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getQuotationReferencedDocument()
    {
        return $this->quotationReferencedDocument;
    }

    /**
     * Sets a new quotationReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $quotationReferencedDocument
     * @return self
     */
    public function setQuotationReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $quotationReferencedDocument = null)
    {
        $this->quotationReferencedDocument = $quotationReferencedDocument;
        return $this;
    }

    /**
     * Gets as contractReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getContractReferencedDocument()
    {
        return $this->contractReferencedDocument;
    }

    /**
     * Sets a new contractReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $contractReferencedDocument
     * @return self
     */
    public function setContractReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $contractReferencedDocument = null)
    {
        $this->contractReferencedDocument = $contractReferencedDocument;
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
     * Gets as buyerAgentTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getBuyerAgentTradeParty()
    {
        return $this->buyerAgentTradeParty;
    }

    /**
     * Sets a new buyerAgentTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerAgentTradeParty
     * @return self
     */
    public function setBuyerAgentTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $buyerAgentTradeParty = null)
    {
        $this->buyerAgentTradeParty = $buyerAgentTradeParty;
        return $this;
    }

    /**
     * Gets as specifiedProcuringProject
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProcuringProjectType
     */
    public function getSpecifiedProcuringProject()
    {
        return $this->specifiedProcuringProject;
    }

    /**
     * Sets a new specifiedProcuringProject
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProcuringProjectType $specifiedProcuringProject
     * @return self
     */
    public function setSpecifiedProcuringProject(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProcuringProjectType $specifiedProcuringProject = null)
    {
        $this->specifiedProcuringProject = $specifiedProcuringProject;
        return $this;
    }

    /**
     * Adds as ultimateCustomerOrderReferencedDocument
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $ultimateCustomerOrderReferencedDocument
     */
    public function addToUltimateCustomerOrderReferencedDocument(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $ultimateCustomerOrderReferencedDocument)
    {
        $this->ultimateCustomerOrderReferencedDocument[] = $ultimateCustomerOrderReferencedDocument;
        return $this;
    }

    /**
     * isset ultimateCustomerOrderReferencedDocument
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetUltimateCustomerOrderReferencedDocument($index)
    {
        return isset($this->ultimateCustomerOrderReferencedDocument[$index]);
    }

    /**
     * unset ultimateCustomerOrderReferencedDocument
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetUltimateCustomerOrderReferencedDocument($index)
    {
        unset($this->ultimateCustomerOrderReferencedDocument[$index]);
    }

    /**
     * Gets as ultimateCustomerOrderReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType[]
     */
    public function getUltimateCustomerOrderReferencedDocument()
    {
        return $this->ultimateCustomerOrderReferencedDocument;
    }

    /**
     * Sets a new ultimateCustomerOrderReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType[] $ultimateCustomerOrderReferencedDocument
     * @return self
     */
    public function setUltimateCustomerOrderReferencedDocument(?array $ultimateCustomerOrderReferencedDocument = null)
    {
        $this->ultimateCustomerOrderReferencedDocument = $ultimateCustomerOrderReferencedDocument;
        return $this;
    }
}
