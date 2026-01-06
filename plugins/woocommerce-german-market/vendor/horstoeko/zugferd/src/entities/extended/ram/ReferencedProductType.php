<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing ReferencedProductType
 *
 * XSD Type: ReferencedProductType
 */
class ReferencedProductType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $iD
     */
    private $iD = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType[] $globalID
     */
    private $globalID = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $sellerAssignedID
     */
    private $sellerAssignedID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $buyerAssignedID
     */
    private $buyerAssignedID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $industryAssignedID
     */
    private $industryAssignedID = null;

    /**
     * @var string $name
     */
    private $name = null;

    /**
     * @var string $description
     */
    private $description = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $unitQuantity
     */
    private $unitQuantity = null;

    /**
     * Gets as iD
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getID()
    {
        return $this->iD;
    }

    /**
     * Sets a new iD
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $iD
     * @return self
     */
    public function setID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $iD = null)
    {
        $this->iD = $iD;
        return $this;
    }

    /**
     * Adds as globalID
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $globalID
     */
    public function addToGlobalID(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $globalID)
    {
        $this->globalID[] = $globalID;
        return $this;
    }

    /**
     * isset globalID
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetGlobalID($index)
    {
        return isset($this->globalID[$index]);
    }

    /**
     * unset globalID
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetGlobalID($index)
    {
        unset($this->globalID[$index]);
    }

    /**
     * Gets as globalID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType[]
     */
    public function getGlobalID()
    {
        return $this->globalID;
    }

    /**
     * Sets a new globalID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType[] $globalID
     * @return self
     */
    public function setGlobalID(?array $globalID = null)
    {
        $this->globalID = $globalID;
        return $this;
    }

    /**
     * Gets as sellerAssignedID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getSellerAssignedID()
    {
        return $this->sellerAssignedID;
    }

    /**
     * Sets a new sellerAssignedID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $sellerAssignedID
     * @return self
     */
    public function setSellerAssignedID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $sellerAssignedID = null)
    {
        $this->sellerAssignedID = $sellerAssignedID;
        return $this;
    }

    /**
     * Gets as buyerAssignedID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getBuyerAssignedID()
    {
        return $this->buyerAssignedID;
    }

    /**
     * Sets a new buyerAssignedID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $buyerAssignedID
     * @return self
     */
    public function setBuyerAssignedID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $buyerAssignedID = null)
    {
        $this->buyerAssignedID = $buyerAssignedID;
        return $this;
    }

    /**
     * Gets as industryAssignedID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getIndustryAssignedID()
    {
        return $this->industryAssignedID;
    }

    /**
     * Sets a new industryAssignedID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $industryAssignedID
     * @return self
     */
    public function setIndustryAssignedID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $industryAssignedID = null)
    {
        $this->industryAssignedID = $industryAssignedID;
        return $this;
    }

    /**
     * Gets as name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a new name
     *
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets as description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets a new description
     *
     * @param  string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Gets as unitQuantity
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType
     */
    public function getUnitQuantity()
    {
        return $this->unitQuantity;
    }

    /**
     * Sets a new unitQuantity
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $unitQuantity
     * @return self
     */
    public function setUnitQuantity(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\QuantityType $unitQuantity = null)
    {
        $this->unitQuantity = $unitQuantity;
        return $this;
    }
}
