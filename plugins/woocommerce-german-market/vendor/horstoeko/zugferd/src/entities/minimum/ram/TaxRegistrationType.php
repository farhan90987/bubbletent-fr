<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram;

/**
 * Class representing TaxRegistrationType
 *
 * XSD Type: TaxRegistrationType
 */
class TaxRegistrationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType $iD
     */
    private $iD = null;

    /**
     * Gets as iD
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType
     */
    public function getID()
    {
        return $this->iD;
    }

    /**
     * Sets a new iD
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType $iD
     * @return self
     */
    public function setID(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType $iD)
    {
        $this->iD = $iD;
        return $this;
    }
}
