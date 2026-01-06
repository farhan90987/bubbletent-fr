<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing HeaderTradeDeliveryType
 *
 * XSD Type: HeaderTradeDeliveryType
 */
class HeaderTradeDeliveryType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradePartyType $shipToTradeParty
     */
    private $shipToTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SupplyChainEventType $actualDeliverySupplyChainEvent
     */
    private $actualDeliverySupplyChainEvent = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $despatchAdviceReferencedDocument
     */
    private $despatchAdviceReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $receivingAdviceReferencedDocument
     */
    private $receivingAdviceReferencedDocument = null;

    /**
     * Gets as shipToTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradePartyType
     */
    public function getShipToTradeParty()
    {
        return $this->shipToTradeParty;
    }

    /**
     * Sets a new shipToTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradePartyType $shipToTradeParty
     * @return self
     */
    public function setShipToTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\TradePartyType $shipToTradeParty = null)
    {
        $this->shipToTradeParty = $shipToTradeParty;
        return $this;
    }

    /**
     * Gets as actualDeliverySupplyChainEvent
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SupplyChainEventType
     */
    public function getActualDeliverySupplyChainEvent()
    {
        return $this->actualDeliverySupplyChainEvent;
    }

    /**
     * Sets a new actualDeliverySupplyChainEvent
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SupplyChainEventType $actualDeliverySupplyChainEvent
     * @return self
     */
    public function setActualDeliverySupplyChainEvent(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\SupplyChainEventType $actualDeliverySupplyChainEvent = null)
    {
        $this->actualDeliverySupplyChainEvent = $actualDeliverySupplyChainEvent;
        return $this;
    }

    /**
     * Gets as despatchAdviceReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType
     */
    public function getDespatchAdviceReferencedDocument()
    {
        return $this->despatchAdviceReferencedDocument;
    }

    /**
     * Sets a new despatchAdviceReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $despatchAdviceReferencedDocument
     * @return self
     */
    public function setDespatchAdviceReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $despatchAdviceReferencedDocument = null)
    {
        $this->despatchAdviceReferencedDocument = $despatchAdviceReferencedDocument;
        return $this;
    }

    /**
     * Gets as receivingAdviceReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType
     */
    public function getReceivingAdviceReferencedDocument()
    {
        return $this->receivingAdviceReferencedDocument;
    }

    /**
     * Sets a new receivingAdviceReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $receivingAdviceReferencedDocument
     * @return self
     */
    public function setReceivingAdviceReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram\ReferencedDocumentType $receivingAdviceReferencedDocument = null)
    {
        $this->receivingAdviceReferencedDocument = $receivingAdviceReferencedDocument;
        return $this;
    }
}
