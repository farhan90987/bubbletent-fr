<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing TradeProductInstanceType
 *
 * XSD Type: TradeProductInstanceType
 */
class TradeProductInstanceType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $batchID
     */
    private $batchID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $supplierAssignedSerialID
     */
    private $supplierAssignedSerialID = null;

    /**
     * Gets as batchID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getBatchID()
    {
        return $this->batchID;
    }

    /**
     * Sets a new batchID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $batchID
     * @return self
     */
    public function setBatchID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $batchID = null)
    {
        $this->batchID = $batchID;
        return $this;
    }

    /**
     * Gets as supplierAssignedSerialID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getSupplierAssignedSerialID()
    {
        return $this->supplierAssignedSerialID;
    }

    /**
     * Sets a new supplierAssignedSerialID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $supplierAssignedSerialID
     * @return self
     */
    public function setSupplierAssignedSerialID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $supplierAssignedSerialID = null)
    {
        $this->supplierAssignedSerialID = $supplierAssignedSerialID;
        return $this;
    }
}
