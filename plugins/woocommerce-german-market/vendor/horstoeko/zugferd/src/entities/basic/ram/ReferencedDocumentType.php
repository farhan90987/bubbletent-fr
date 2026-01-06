<?php

namespace MarketPress\German_Market\horstoeko\zugferd\entities\basic\ram;

/**
 * Class representing ReferencedDocumentType
 *
 * XSD Type: ReferencedDocumentType
 */
class ReferencedDocumentType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $issuerAssignedID
     */
    private $issuerAssignedID = null;

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\basic\qdt\FormattedDateTimeType $formattedIssueDateTime
     */
    private $formattedIssueDateTime = null;

    /**
     * Gets as issuerAssignedID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType
     */
    public function getIssuerAssignedID()
    {
        return $this->issuerAssignedID;
    }

    /**
     * Sets a new issuerAssignedID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $issuerAssignedID
     * @return self
     */
    public function setIssuerAssignedID(\MarketPress\German_Market\horstoeko\zugferd\entities\basic\udt\IDType $issuerAssignedID)
    {
        $this->issuerAssignedID = $issuerAssignedID;
        return $this;
    }

    /**
     * Gets as formattedIssueDateTime
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\basic\qdt\FormattedDateTimeType
     */
    public function getFormattedIssueDateTime()
    {
        return $this->formattedIssueDateTime;
    }

    /**
     * Sets a new formattedIssueDateTime
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\basic\qdt\FormattedDateTimeType $formattedIssueDateTime
     * @return self
     */
    public function setFormattedIssueDateTime(?\MarketPress\German_Market\horstoeko\zugferd\entities\basic\qdt\FormattedDateTimeType $formattedIssueDateTime = null)
    {
        $this->formattedIssueDateTime = $formattedIssueDateTime;
        return $this;
    }
}
