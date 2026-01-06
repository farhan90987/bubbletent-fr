<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing TradeProductType
 *
 * XSD Type: TradeProductType
 */
class TradeProductType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $iD
     */
    private $iD = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $globalID
     */
    private $globalID = null;

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
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $modelID
     */
    private $modelID = null;

    /**
     * @var string $name
     */
    private $name = null;

    /**
     * @var string $description
     */
    private $description = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType[] $batchID
     */
    private $batchID = [
        
    ];

    /**
     * @var string $brandName
     */
    private $brandName = null;

    /**
     * @var string $modelName
     */
    private $modelName = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductCharacteristicType[] $applicableProductCharacteristic
     */
    private $applicableProductCharacteristic = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductClassificationType[] $designatedProductClassification
     */
    private $designatedProductClassification = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeProductInstanceType[] $individualTradeProductInstance
     */
    private $individualTradeProductInstance = [
        
    ];

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeCountryType $originTradeCountry
     */
    private $originTradeCountry = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedProductType[] $includedReferencedProduct
     */
    private $includedReferencedProduct = [
        
    ];

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
     * Gets as globalID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getGlobalID()
    {
        return $this->globalID;
    }

    /**
     * Sets a new globalID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $globalID
     * @return self
     */
    public function setGlobalID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $globalID = null)
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
     * Gets as modelID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType
     */
    public function getModelID()
    {
        return $this->modelID;
    }

    /**
     * Sets a new modelID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $modelID
     * @return self
     */
    public function setModelID(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $modelID = null)
    {
        $this->modelID = $modelID;
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
     * Adds as batchID
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $batchID
     */
    public function addToBatchID(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $batchID)
    {
        $this->batchID[] = $batchID;
        return $this;
    }

    /**
     * isset batchID
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetBatchID($index)
    {
        return isset($this->batchID[$index]);
    }

    /**
     * unset batchID
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetBatchID($index)
    {
        unset($this->batchID[$index]);
    }

    /**
     * Gets as batchID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType[]
     */
    public function getBatchID()
    {
        return $this->batchID;
    }

    /**
     * Sets a new batchID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType[] $batchID
     * @return self
     */
    public function setBatchID(?array $batchID = null)
    {
        $this->batchID = $batchID;
        return $this;
    }

    /**
     * Gets as brandName
     *
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * Sets a new brandName
     *
     * @param  string $brandName
     * @return self
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
        return $this;
    }

    /**
     * Gets as modelName
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * Sets a new modelName
     *
     * @param  string $modelName
     * @return self
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    /**
     * Adds as applicableProductCharacteristic
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductCharacteristicType $applicableProductCharacteristic
     */
    public function addToApplicableProductCharacteristic(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductCharacteristicType $applicableProductCharacteristic)
    {
        $this->applicableProductCharacteristic[] = $applicableProductCharacteristic;
        return $this;
    }

    /**
     * isset applicableProductCharacteristic
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetApplicableProductCharacteristic($index)
    {
        return isset($this->applicableProductCharacteristic[$index]);
    }

    /**
     * unset applicableProductCharacteristic
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetApplicableProductCharacteristic($index)
    {
        unset($this->applicableProductCharacteristic[$index]);
    }

    /**
     * Gets as applicableProductCharacteristic
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductCharacteristicType[]
     */
    public function getApplicableProductCharacteristic()
    {
        return $this->applicableProductCharacteristic;
    }

    /**
     * Sets a new applicableProductCharacteristic
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductCharacteristicType[] $applicableProductCharacteristic
     * @return self
     */
    public function setApplicableProductCharacteristic(?array $applicableProductCharacteristic = null)
    {
        $this->applicableProductCharacteristic = $applicableProductCharacteristic;
        return $this;
    }

    /**
     * Adds as designatedProductClassification
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductClassificationType $designatedProductClassification
     */
    public function addToDesignatedProductClassification(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductClassificationType $designatedProductClassification)
    {
        $this->designatedProductClassification[] = $designatedProductClassification;
        return $this;
    }

    /**
     * isset designatedProductClassification
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetDesignatedProductClassification($index)
    {
        return isset($this->designatedProductClassification[$index]);
    }

    /**
     * unset designatedProductClassification
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetDesignatedProductClassification($index)
    {
        unset($this->designatedProductClassification[$index]);
    }

    /**
     * Gets as designatedProductClassification
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductClassificationType[]
     */
    public function getDesignatedProductClassification()
    {
        return $this->designatedProductClassification;
    }

    /**
     * Sets a new designatedProductClassification
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ProductClassificationType[] $designatedProductClassification
     * @return self
     */
    public function setDesignatedProductClassification(?array $designatedProductClassification = null)
    {
        $this->designatedProductClassification = $designatedProductClassification;
        return $this;
    }

    /**
     * Adds as individualTradeProductInstance
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeProductInstanceType $individualTradeProductInstance
     */
    public function addToIndividualTradeProductInstance(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeProductInstanceType $individualTradeProductInstance)
    {
        $this->individualTradeProductInstance[] = $individualTradeProductInstance;
        return $this;
    }

    /**
     * isset individualTradeProductInstance
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetIndividualTradeProductInstance($index)
    {
        return isset($this->individualTradeProductInstance[$index]);
    }

    /**
     * unset individualTradeProductInstance
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetIndividualTradeProductInstance($index)
    {
        unset($this->individualTradeProductInstance[$index]);
    }

    /**
     * Gets as individualTradeProductInstance
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeProductInstanceType[]
     */
    public function getIndividualTradeProductInstance()
    {
        return $this->individualTradeProductInstance;
    }

    /**
     * Sets a new individualTradeProductInstance
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeProductInstanceType[] $individualTradeProductInstance
     * @return self
     */
    public function setIndividualTradeProductInstance(?array $individualTradeProductInstance = null)
    {
        $this->individualTradeProductInstance = $individualTradeProductInstance;
        return $this;
    }

    /**
     * Gets as originTradeCountry
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeCountryType
     */
    public function getOriginTradeCountry()
    {
        return $this->originTradeCountry;
    }

    /**
     * Sets a new originTradeCountry
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeCountryType $originTradeCountry
     * @return self
     */
    public function setOriginTradeCountry(?\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\TradeCountryType $originTradeCountry = null)
    {
        $this->originTradeCountry = $originTradeCountry;
        return $this;
    }

    /**
     * Adds as includedReferencedProduct
     *
     * @return self
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedProductType $includedReferencedProduct
     */
    public function addToIncludedReferencedProduct(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedProductType $includedReferencedProduct)
    {
        $this->includedReferencedProduct[] = $includedReferencedProduct;
        return $this;
    }

    /**
     * isset includedReferencedProduct
     *
     * @param  int|string $index
     * @return bool
     */
    public function issetIncludedReferencedProduct($index)
    {
        return isset($this->includedReferencedProduct[$index]);
    }

    /**
     * unset includedReferencedProduct
     *
     * @param  int|string $index
     * @return void
     */
    public function unsetIncludedReferencedProduct($index)
    {
        unset($this->includedReferencedProduct[$index]);
    }

    /**
     * Gets as includedReferencedProduct
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedProductType[]
     */
    public function getIncludedReferencedProduct()
    {
        return $this->includedReferencedProduct;
    }

    /**
     * Sets a new includedReferencedProduct
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram\ReferencedProductType[] $includedReferencedProduct
     * @return self
     */
    public function setIncludedReferencedProduct(?array $includedReferencedProduct = null)
    {
        $this->includedReferencedProduct = $includedReferencedProduct;
        return $this;
    }
}
