<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram;

/**
 * Class representing ExchangedDocumentType
 *
 * XSD Type: ExchangedDocumentType
 */
class ExchangedDocumentType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType $iD
     */
    private $iD = null;

    /**
     * @var string $typeCode
     */
    private $typeCode = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\DateTimeType $issueDateTime
     */
    private $issueDateTime = null;

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

    /**
     * Gets as typeCode
     *
     * @return string
     */
    public function getTypeCode()
    {
        return $this->typeCode;
    }

    /**
     * Sets a new typeCode
     *
     * @param  string $typeCode
     * @return self
     */
    public function setTypeCode($typeCode)
    {
        $this->typeCode = $typeCode;
        return $this;
    }

    /**
     * Gets as issueDateTime
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\DateTimeType
     */
    public function getIssueDateTime()
    {
        return $this->issueDateTime;
    }

    /**
     * Sets a new issueDateTime
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\DateTimeType $issueDateTime
     * @return self
     */
    public function setIssueDateTime(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\DateTimeType $issueDateTime)
    {
        $this->issueDateTime = $issueDateTime;
        return $this;
    }
}
