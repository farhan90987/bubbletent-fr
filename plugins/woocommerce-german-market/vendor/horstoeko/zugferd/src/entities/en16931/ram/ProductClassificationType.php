<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing ProductClassificationType
 *
 * XSD Type: ProductClassificationType
 */
class ProductClassificationType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\CodeType $classCode
     */
    private $classCode = null;

    /**
     * Gets as classCode
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\CodeType
     */
    public function getClassCode()
    {
        return $this->classCode;
    }

    /**
     * Sets a new classCode
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\CodeType $classCode
     * @return self
     */
    public function setClassCode(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\CodeType $classCode = null)
    {
        $this->classCode = $classCode;
        return $this;
    }
}
