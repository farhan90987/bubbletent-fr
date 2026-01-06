<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing LineTradeDeliveryType
 *
 * XSD Type: LineTradeDeliveryType
 */
class LineTradeDeliveryType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $billedQuantity
     */
    private $billedQuantity = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $chargeFreeQuantity
     */
    private $chargeFreeQuantity = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $packageQuantity
     */
    private $packageQuantity = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $shipToTradeParty
     */
    private $shipToTradeParty = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradePartyType $ultimateShipToTradeParty
     */
    private $ultimateShipToTradeParty = null;

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
     * Gets as billedQuantity
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType
     */
    public function getBilledQuantity()
    {
        return $this->billedQuantity;
    }

    /**
     * Sets a new billedQuantity
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $billedQuantity
     * @return self
     */
    public function setBilledQuantity(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $billedQuantity)
    {
        $this->billedQuantity = $billedQuantity;
        return $this;
    }

    /**
     * Gets as chargeFreeQuantity
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType
     */
    public function getChargeFreeQuantity()
    {
        return $this->chargeFreeQuantity;
    }

    /**
     * Sets a new chargeFreeQuantity
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $chargeFreeQuantity
     * @return self
     */
    public function setChargeFreeQuantity(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $chargeFreeQuantity = null)
    {
        $this->chargeFreeQuantity = $chargeFreeQuantity;
        return $this;
    }

    /**
     * Gets as packageQuantity
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType
     */
    public function getPackageQuantity()
    {
        return $this->packageQuantity;
    }

    /**
     * Sets a new packageQuantity
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $packageQuantity
     * @return self
     */
    public function setPackageQuantity(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $packageQuantity = null)
    {
        $this->packageQuantity = $packageQuantity;
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
