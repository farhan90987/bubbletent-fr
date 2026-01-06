<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing HeaderTradeDeliveryType
 *
 * XSD Type: HeaderTradeDeliveryType
 */
class HeaderTradeDeliveryType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\LogisticsTransportMovementType[] $relatedSupplyChainConsignment
     */
    private $relatedSupplyChainConsignment = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $shipToTradeParty
     */
    private $shipToTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $ultimateShipToTradeParty
     */
    private $ultimateShipToTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $shipFromTradeParty
     */
    private $shipFromTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainEventType $actualDeliverySupplyChainEvent
     */
    private $actualDeliverySupplyChainEvent = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $despatchAdviceReferencedDocument
     */
    private $despatchAdviceReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $receivingAdviceReferencedDocument
     */
    private $receivingAdviceReferencedDocument = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $deliveryNoteReferencedDocument
     */
    private $deliveryNoteReferencedDocument = null;

    /**
     * Adds as specifiedLogisticsTransportMovement
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\LogisticsTransportMovementType $specifiedLogisticsTransportMovement
     */
    public function addToRelatedSupplyChainConsignment(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\LogisticsTransportMovementType $specifiedLogisticsTransportMovement)
    {
        $this->relatedSupplyChainConsignment[] = $specifiedLogisticsTransportMovement;
        return $this;
    }

    /**
     * isset relatedSupplyChainConsignment
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetRelatedSupplyChainConsignment($index)
    {
        return isset($this->relatedSupplyChainConsignment[$index]);
    }

    /**
     * unset relatedSupplyChainConsignment
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetRelatedSupplyChainConsignment($index)
    {
        unset($this->relatedSupplyChainConsignment[$index]);
    }

    /**
     * Gets as relatedSupplyChainConsignment
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\LogisticsTransportMovementType[]
     */
    public function getRelatedSupplyChainConsignment()
    {
        return $this->relatedSupplyChainConsignment;
    }

    /**
     * Sets a new relatedSupplyChainConsignment
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\LogisticsTransportMovementType[] $relatedSupplyChainConsignment
     * @return self
     */
    public function setRelatedSupplyChainConsignment(?array $relatedSupplyChainConsignment = null)
    {
        $this->relatedSupplyChainConsignment = $relatedSupplyChainConsignment;
        return $this;
    }

    /**
     * Gets as shipToTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getShipToTradeParty()
    {
        return $this->shipToTradeParty;
    }

    /**
     * Sets a new shipToTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $shipToTradeParty
     * @return self
     */
    public function setShipToTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $shipToTradeParty = null)
    {
        $this->shipToTradeParty = $shipToTradeParty;
        return $this;
    }

    /**
     * Gets as ultimateShipToTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getUltimateShipToTradeParty()
    {
        return $this->ultimateShipToTradeParty;
    }

    /**
     * Sets a new ultimateShipToTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $ultimateShipToTradeParty
     * @return self
     */
    public function setUltimateShipToTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $ultimateShipToTradeParty = null)
    {
        $this->ultimateShipToTradeParty = $ultimateShipToTradeParty;
        return $this;
    }

    /**
     * Gets as shipFromTradeParty
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType
     */
    public function getShipFromTradeParty()
    {
        return $this->shipFromTradeParty;
    }

    /**
     * Sets a new shipFromTradeParty
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $shipFromTradeParty
     * @return self
     */
    public function setShipFromTradeParty(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $shipFromTradeParty = null)
    {
        $this->shipFromTradeParty = $shipFromTradeParty;
        return $this;
    }

    /**
     * Gets as actualDeliverySupplyChainEvent
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainEventType
     */
    public function getActualDeliverySupplyChainEvent()
    {
        return $this->actualDeliverySupplyChainEvent;
    }

    /**
     * Sets a new actualDeliverySupplyChainEvent
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainEventType $actualDeliverySupplyChainEvent
     * @return self
     */
    public function setActualDeliverySupplyChainEvent(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\SupplyChainEventType $actualDeliverySupplyChainEvent = null)
    {
        $this->actualDeliverySupplyChainEvent = $actualDeliverySupplyChainEvent;
        return $this;
    }

    /**
     * Gets as despatchAdviceReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getDespatchAdviceReferencedDocument()
    {
        return $this->despatchAdviceReferencedDocument;
    }

    /**
     * Sets a new despatchAdviceReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $despatchAdviceReferencedDocument
     * @return self
     */
    public function setDespatchAdviceReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $despatchAdviceReferencedDocument = null)
    {
        $this->despatchAdviceReferencedDocument = $despatchAdviceReferencedDocument;
        return $this;
    }

    /**
     * Gets as receivingAdviceReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getReceivingAdviceReferencedDocument()
    {
        return $this->receivingAdviceReferencedDocument;
    }

    /**
     * Sets a new receivingAdviceReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $receivingAdviceReferencedDocument
     * @return self
     */
    public function setReceivingAdviceReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $receivingAdviceReferencedDocument = null)
    {
        $this->receivingAdviceReferencedDocument = $receivingAdviceReferencedDocument;
        return $this;
    }

    /**
     * Gets as deliveryNoteReferencedDocument
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType
     */
    public function getDeliveryNoteReferencedDocument()
    {
        return $this->deliveryNoteReferencedDocument;
    }

    /**
     * Sets a new deliveryNoteReferencedDocument
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $deliveryNoteReferencedDocument
     * @return self
     */
    public function setDeliveryNoteReferencedDocument(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedDocumentType $deliveryNoteReferencedDocument = null)
    {
        $this->deliveryNoteReferencedDocument = $deliveryNoteReferencedDocument;
        return $this;
    }
}
