<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\extended\ram;

/**
 * Class representing DocumentContextParameterType
 *
 * XSD Type: DocumentContextParameterType
 */
class DocumentContextParameterType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $iD
     */
    private $iD = null;

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
    public function setID(\MarketPress\German_Market\horstoeko\zugferd\entities\extended\udt\IDType $iD)
    {
        $this->iD = $iD;
        return $this;
    }
}
