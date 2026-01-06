<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\ram;

/**
 * Class representing UniversalCommunicationType
 *
 * XSD Type: UniversalCommunicationType
 */
class UniversalCommunicationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $uRIID
     */
    private $uRIID = null;

    /**
     * Gets as uRIID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType
     */
    public function getURIID()
    {
        return $this->uRIID;
    }

    /**
     * Sets a new uRIID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $uRIID
     * @return self
     */
    public function setURIID(\MarketPress\German_Market\horstoeko\zugferd\entities\basicwl\udt\IDType $uRIID)
    {
        $this->uRIID = $uRIID;
        return $this;
    }
}
