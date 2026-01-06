<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram;

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
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType $sellerTradeParty
     */
    private $sellerTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType $buyerTradeParty
     */
    private $buyerTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ReferencedDocumentType $buyerOrderReferencedDocument
     */
    private $buyerOrderReferencedDocument = null;

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
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType
     */
    public function getSellerTradeParty()
    {
        return $this->sellerTradeParty;
    }

    /**
     * Sets a new sellerTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType $sellerTradeParty
     * @return self
     */
    public function setSellerTradeParty(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType $sellerTradeParty)
    {
        $this->sellerTradeParty = $sellerTradeParty;
        return $this;
    }

    /**
     * Gets as buyerTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType
     */
    public function getBuyerTradeParty()
    {
        return $this->buyerTradeParty;
    }

    /**
     * Sets a new buyerTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType $buyerTradeParty
     * @return self
     */
    public function setBuyerTradeParty(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\TradePartyType $buyerTradeParty)
    {
        $this->buyerTradeParty = $buyerTradeParty;
        return $this;
    }

    /**
     * Gets as buyerOrderReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ReferencedDocumentType
     */
    public function getBuyerOrderReferencedDocument()
    {
        return $this->buyerOrderReferencedDocument;
    }

    /**
     * Sets a new buyerOrderReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ReferencedDocumentType $buyerOrderReferencedDocument
     * @return self
     */
    public function setBuyerOrderReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram\ReferencedDocumentType $buyerOrderReferencedDocument = null)
    {
        $this->buyerOrderReferencedDocument = $buyerOrderReferencedDocument;
        return $this;
    }
}
