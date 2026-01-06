<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing UniversalCommunicationType
 *
 * XSD Type: UniversalCommunicationType
 */
class UniversalCommunicationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $uRIID
     */
    private $uRIID = null;

    /**
     * @var string $completeNumber
     */
    private $completeNumber = null;

    /**
     * Gets as uRIID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType
     */
    public function getURIID()
    {
        return $this->uRIID;
    }

    /**
     * Sets a new uRIID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $uRIID
     * @return self
     */
    public function setURIID(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\IDType $uRIID = null)
    {
        $this->uRIID = $uRIID;
        return $this;
    }

    /**
     * Gets as completeNumber
     *
     * @return string
     */
    public function getCompleteNumber()
    {
        return $this->completeNumber;
    }

    /**
     * Sets a new completeNumber
     *
     * @param  string $completeNumber
     * @return self
     */
    public function setCompleteNumber($completeNumber)
    {
        $this->completeNumber = $completeNumber;
        return $this;
    }
}
